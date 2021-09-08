<?php

namespace App\Service;

use App\Notification\IncomingPhoneNotification;
use App\Repository\MemberRepository;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twilio\TwiML\VoiceResponse;

class PhoneService
{
    protected $params;

    protected $notifier;

    protected $communicationLogService;

    protected $memberRepository;

    protected $urlGenerator;

    protected $twilioDsn;

    public function __construct(ParameterBagInterface $params, NotifierInterface $notifier, CommunicationLogService $communicationLogService, UrlGeneratorInterface $urlGenerator, MemberRepository $memberRepository)
    {
        $this->params = $params;
        $this->notifier = $notifier;
        $this->communicationLogService = $communicationLogService;
        $this->memberRepository = $memberRepository;
        $this->urlGenerator = $urlGenerator;
        $this->twilioDsn = isset($_ENV['TWILIO_DSN']) ? $_ENV['TWILIO_DSN'] : null;
    }

    public function isConfigured(): bool
    {
        if ($this->twilioDsn) {
            return true;
        }

        return false;
    }

    public function getWebhookToken(): string
    {
        return md5($this->twilioDsn);
    }

    public function getFromTelephoneNumber(): string
    {
        if (!$this->isConfigured()) {
            return 'Not configured.';
        }
        if (preg_match('/from\=(.*)/i', $this->twilioDsn, $matches)) {
            return preg_replace(
                '/.*(\d{3})[^\d]{0,7}(\d{3})[^\d]{0,7}(\d{4}).*/',
                '($1) $2-$3',
                $matches[0]
            );
        }

        return 'Unable to parse telephone number.';
    }

    public function handleWebhook(Request $request): VoiceResponse
    {
        $fromTelephone = $request->request->get('From', '');
        $callStatus = $request->request->get('CallStatus', '');
        $recordingUrl = $request->request->get('RecordingUrl', '');
        $recordingDuration = $request->request->get('RecordingDuration', '');

        if (!$fromTelephone || !$callStatus) {
            throw new \Exception('Invalid payload, must include a `From` and `CallStatus`.');
        }

        $response = new VoiceResponse();

        // Initial Call
        if (in_array($callStatus, ['queued', 'ringing'])) {
            $response->say($this->params->get('app.voicemail.message'), [
                'voice' => $this->params->get('app.voicemail.voice'),
                'language' => $this->params->get('app.voicemail.language'),
            ]);
            $response->record(['timeout' => 10, 'maxLength' => 300]);
            $response->say('I was unable to record your message.');
            $response->hangup();

            return $response;
        }

        // Do nothing for incomplete call states
        if (!in_array($callStatus, ['completed', 'busy', 'no-answer', 'canceled', 'failed'])) {
            return $response;
        }

        try {
            $member = $this->memberRepository->findOneByPrimaryTelephone($fromTelephone);
            $options = [
                'action_text' => 'Return Call',
                'action_url' => sprintf('tel:%s', $fromTelephone),
            ];
        } catch (\Exception $e) {
            $member = null;
            $options = [];
        }
        $logEntry = sprintf(
            "From: %s  \nTelephone: %s  \n---  \n%s",
            $member ? $member : 'Unknown Caller',
            $fromTelephone,
            'No voicemail recorded'
        );
        if ($recordingUrl && $recordingDuration) {
            $logEntry = sprintf(
                "From: %s  \nTelephone: %s  \n---  \n%s  \n  \nURL: %s  \nDuration: %d seconds",
                $member ? $member : 'Unknown Caller',
                $fromTelephone,
                'Voicemail recorded.',
                $recordingUrl,
                $recordingDuration
            );
        }
        if ($member) {
            $this->communicationLogService->log(
                'Telephone Call',
                $logEntry,
                $member,
                null,
                $request->request->all()
            );
        }
        $notification = new IncomingPhoneNotification($member, $options);
        $notification->content($logEntry);
        $this->notifier->send($notification, ...$this->notifier->getAdminRecipients());

        return $response;
    }
}
