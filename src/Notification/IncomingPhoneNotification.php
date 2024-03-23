<?php

namespace App\Notification;

use App\Entity\Member;
use Symfony\Component\Notifier\Message\EmailMessage;
use Symfony\Component\Notifier\Notification\EmailNotificationInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\Recipient\EmailRecipientInterface;

class IncomingPhoneNotification extends Notification implements EmailNotificationInterface
{
    protected $options = [];

    public function __construct(?Member $member = null, $options = [])
    {
        if ($member) {
            parent::__construct(sprintf('Phone Call from %s', $member), ['email']);
        } else {
            parent::__construct('Phone Call', ['email']);
        }
        $this->options = $options;
    }

    public function asEmailMessage(EmailRecipientInterface $recipient, ?string $transport = null): ?EmailMessage
    {
        $message = EmailMessage::fromNotification($this, $recipient);
        $message->getMessage()->getHeaders()->addTextHeader('X-Cmail-GroupName', 'Incoming Phone Notification'); // @phpstan-ignore-line
        $message->getMessage()->getHeaders()->addTextHeader('X-MC-Tags', 'Incoming Phone Notification'); // @phpstan-ignore-line
        if (isset($this->options['action_text'], $this->options['action_text'])) {
            $message->getMessage()->action($this->options['action_text'], $this->options['action_url']); // @phpstan-ignore-line
        }
        $message->getMessage()->markAsPublic(); // @phpstan-ignore-line

        return $message;
    }
}
