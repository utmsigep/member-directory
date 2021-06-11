<?php

namespace App\Service;

use App\Entity\Member;
use App\Entity\User;
use App\Notification\IncomingPhoneNotification;
use App\Repository\MemberRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twilio\TwiML\MessagingResponse;

class PhoneService
{
    protected $notifier;

    protected $communicationLogService;

    protected $memberRepository;

    protected $urlGenerator;

    public function __construct(NotifierInterface $notifier, CommunicationLogService $communicationLogService, UrlGeneratorInterface $urlGenerator, MemberRepository $memberRepository)
    {
        $this->notifier = $notifier;
        $this->communicationLogService = $communicationLogService;
        $this->memberRepository = $memberRepository;
        $this->urlGenerator = $urlGenerator;
    }

    public function isConfigured(): bool
    {
        if (isset($_ENV['TWILIO_DSN']) && $_ENV['TWILIO_DSN']) {
            return true;
        }
        return false;
    }

    public function getWebhookToken(): string
    {
        return md5($_ENV['TWILIO_DSN']);
    }

    public function getFromTelephoneNumber(): string
    {
        if (!$this->isConfigured()) {
            return 'Not configured.';
        }
        if (preg_match('/from\=(.*)/i', $_ENV['TWILIO_DSN'], $matches)) {
            return preg_replace(
                '/.*(\d{3})[^\d]{0,7}(\d{3})[^\d]{0,7}(\d{4}).*/',
                '($1) $2-$3',
                $matches[0]
            );
        }
        return 'Unable to parse telephone number.';
    }

    public function handleWebhook(Request $request): MessagingResponse
    {
        $fromTelephone = $request->request->get('From', '');
        $messageBody = $request->request->get('Body', '');
        $recordingUrl = $request->request->get('RecordingUrl', '');
        $recordingDuration = $request->request->get('RecordingDuration', '');
        if (!$fromTelephone || !$messageBody) {
            throw new \Exception('Invalid payload, must include a `From` and `Body`.');
        }
        try {
            $member = $this->memberRepository->findOneByPrimaryTelephone($fromTelephone);
            $options = [
                'action_text' => 'Return Call',
                'action_url' => sprintf('tel:%s', $fromTelephone)
            ];
        } catch (\Exception $e) {
            $member = null;
            $options = [];
        }
        $logEntry = sprintf(
            "From: %s  \nTelephone: %s  \n---  \n%s  \n  \nURL: %s  \nDuration: %s",
            $member ? $member : 'Unknown Caller',
            $fromTelephone,
            $messageBody,
            $recordingUrl,
            $recordingDuration
        );
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

        $response = new MessagingResponse();
        return $response;
    }
}
