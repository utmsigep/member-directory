<?php

namespace App\Notification;

use App\Entity\Member;
use Symfony\Component\Notifier\Message\EmailMessage;
use Symfony\Component\Notifier\Notification\EmailNotificationInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\Recipient\EmailRecipientInterface;

class IncomingSmsNotification extends Notification implements EmailNotificationInterface
{
    public function __construct(Member $member = null)
    {
        if ($member) {
            parent::__construct(sprintf('Text Message from %s', $member), ['email']);
        } else {
            parent::__construct('Text Message', ['email']);
        }
    }

    public function asEmailMessage(EmailRecipientInterface $recipient, string $transport = null): ?EmailMessage
    {
        $message = EmailMessage::fromNotification($this, $recipient);
        $message->getMessage();
        return $message;
    }
}
