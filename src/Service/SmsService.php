<?php

namespace App\Service;

use App\Entity\Member;
use App\Entity\User;
use App\Notification\IncomingSmsNotification;
use App\Repository\MemberRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SmsService
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

    public function sendMemberSms(string $message, Member $member, User $actor): void
    {
        $notification = (new Notification($message, ['sms']));
        $recipient = new Recipient(
            (string) $member->getPrimaryEmail(),
            (string) $member->getPrimaryTelephoneNumber()
        );
        $this->notifier->send($notification, $recipient);
        $this->communicationLogService->log(
            'Text Message',
            sprintf(
                "To: %s  \nTelephone: %s  \n---  \n%s",
                $member,
                $member->getPrimaryTelephoneNumber(),
                $message
            ),
            $member,
            $actor
        );
    }

    public function handleWebhook(Request $request): array
    {
        $fromTelephone = $request->request->get('From', '');
        $messageBody = $request->request->get('Body', '');
        if (!$fromTelephone || !$messageBody) {
            throw new \Exception('Invalid payload, must include a `From` and `Body`.');
        }
        $member = $this->memberRepository->findOneByPrimaryTelephone($fromTelephone);
        $logEntry = sprintf(
            "From: %s  \nTelephone: %s  \n---  \n%s",
            $member,
            $member->getPrimaryTelephoneNumber(),
            $messageBody
        );
        $this->communicationLogService->log(
            'Text Message',
            $logEntry,
            $member,
            null
        );
        $notification = new IncomingSmsNotification($member);
        $notification->content($logEntry);
        $this->notifier->send($notification, ...$this->notifier->getAdminRecipients()
            );
        return [
            'member' => (string) $member,
            'from' => $fromTelephone,
            'message' => $messageBody
        ];
    }
}
