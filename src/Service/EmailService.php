<?php

namespace App\Service;

use App\Entity\Member;
use App\Entity\User;
use CS_REST_Campaigns;
use CS_REST_Lists;
use CS_REST_Subscribers;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordToken;

class EmailService
{
    protected $params;

    protected $router;

    protected $subscribersClient;

    protected $campaignsClient;

    protected $listsClient;

    protected $em;

    protected $mailer;

    protected $communicationLogService;

    public function __construct(ParameterBagInterface $params, UrlGeneratorInterface $router, EntityManagerInterface $em, MailerInterface $mailer, CommunicationLogService $communicationLogService)
    {
        $this->params = $params;
        $this->router = $router;
        $this->subscribersClient = new CS_REST_Subscribers(
            $params->get('campaign_monitor.default_list_id'),
            [
                'api_key' => $params->get('campaign_monitor.api_key')
            ]
        );
        $this->campaignsClient = new CS_REST_Campaigns(
            $params->get('campaign_monitor.default_list_id'),
            [
                'api_key' => $params->get('campaign_monitor.api_key')
            ]
        );
        $this->listsClient = new CS_REST_Lists(
            $params->get('campaign_monitor.default_list_id'),
            [
                'api_key' => $params->get('campaign_monitor.api_key')
            ]
        );
        $this->em = $em;
        $this->mailer = $mailer;
        $this->communicationLogService = $communicationLogService;
    }

    public function isConfigured(): bool
    {
        if ($this->params->has('campaign_monitor.api_key')
            && $this->params->get('campaign_monitor.api_key')
            && $this->params->has('campaign_monitor.default_list_id')
            && $this->params->get('campaign_monitor.default_list_id')
        ) {
            return true;
        }
        return false;
    }

    public function getMemberSubscription(Member $member)
    {
        if (!$member->getPrimaryEmail()) {
            return [];
        }
        $result = $this->subscribersClient->get($member->getPrimaryEmail(), true);
        return $result->response;
    }

    public function getMemberSubscriptionHistory(Member $member)
    {
        if (!$member->getPrimaryEmail()) {
            return [];
        }
        $result = $this->subscribersClient->get_history($member->getPrimaryEmail());
        return $result->response;
    }

    public function subscribeMember(Member $member, $resubscribe = false): bool
    {
        if (!$member->getPrimaryEmail()
            || $member->getIsLocalDoNotContact()
            || $member->getStatus()->getIsInactive()
        ) {
            return false;
        }
        $result = $this->subscribersClient->add([
            'EmailAddress' => $member->getPrimaryEmail(),
            'Name' => $member->getDisplayName(),
            'CustomFields' => $this->buildCustomFieldArray($member),
            'ConsentToTrack' => 'yes',
            'Resubscribe' => $resubscribe
        ]);
        if ($result->was_successful()) {
            return true;
        }
        error_log(json_encode($result->response));
        return false;
    }

    public function updateMember(string $existingEmail, Member $member): bool
    {
        if (!$member->getPrimaryEmail()) {
            return false;
        }
        $result = $this->subscribersClient->update($existingEmail, [
            'EmailAddress' => $member->getPrimaryEmail(),
            'Name' => $member->getDisplayName(),
            'CustomFields' => $this->buildCustomFieldArray($member),
            'ConsentToTrack' => 'yes'
        ]);
        if ($result->was_successful()) {
            return true;
        }
        error_log(json_encode($result->response));
        return false;
    }

    public function unsubscribeMember(Member $member): bool
    {
        if (!$member->getPrimaryEmail()) {
            return false;
        }
        $result = $this->subscribersClient->unsubscribe($member->getPrimaryEmail());
        if ($result->was_successful()) {
            return true;
        }
        error_log(json_encode($result->response));
        return false;
    }

    public function deleteMember(Member $member): bool
    {
        if (!$member->getPrimaryEmail()) {
            return false;
        }
        $result = $this->subscribersClient->delete($member->getPrimaryEmail());
        if ($result->was_successful()) {
            return true;
        }
        error_log(json_encode($result->response));
        return false;
    }

    public function getCampaignById($campaignId): object
    {
        $this->campaignsClient->set_campaign_id($campaignId);
        $result = $this->campaignsClient->get_summary();
        return $result->response;
    }

    public function getWebhooks(): array
    {
        $result = $this->listsClient->get_webhooks();
        if ($result->was_successful()) {
            return $result->response;
        }
        error_log(json_encode($result->response));
        return [];
    }

    public function createWebhook(): ?string
    {
        if (!$this->getWebhookToken()) {
            error_log('No Webhook Token configured.');
            return null;
        }

        $result = $this->listsClient->create_webhook(array(
            'Events' => array(CS_REST_LIST_WEBHOOK_SUBSCRIBE, CS_REST_LIST_WEBHOOK_DEACTIVATE, CS_REST_LIST_WEBHOOK_UPDATE),
            'Url' => $this->router->generate(
                'webhook_email_service',
                [
                    'token' => $this->getWebhookToken()
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
            'PayloadFormat' => CS_REST_WEBHOOK_FORMAT_JSON
        ));
        if ($result->was_successful()) {
            return $result->response;
        }
        error_log(json_encode($result->response));
        return null;
    }

    public function deleteWebhook(string $webhookId): bool
    {
        $result = $this->listsClient->delete_webhook($webhookId);
        if ($result->was_successful()) {
            return true;
        }
        error_log(json_encode($result->response));
        return false;
    }

    public function getWebhookToken(): string
    {
        return md5($this->params->get('campaign_monitor.api_key'));
    }

    public function processWebhookBody(string $content): array
    {
        $content = json_decode($content, false, 512, JSON_THROW_ON_ERROR);
        if (!property_exists($content, 'Events') || !is_array($content->Events)) {
            throw new \Exception('Invalid webhook payload. Must have Events.');
        }
        $memberRepository = $this->em->getRepository(Member::class);
        $output = [];
        foreach ($content->Events as $event) {
            switch($event->Type) {
                case 'Update':
                    $member = $memberRepository->findOneBy([
                        'primaryEmail' => $event->OldEmailAddress
                    ]);
                    if (!$member) {
                        $output[] = [
                            'result' => sprintf(
                                'Unable to locate member with %s',
                                $event->OldEmailAddress
                            ),
                            'payload' => $event
                        ];
                        break;
                    }
                    if (
                        $event->OldEmailAddress === $event->EmailAddress
                        || $event->EmailAddress === $member->getPrimaryEmail()
                    ) {
                        $output[] = [
                            'result' => sprintf(
                                'Email for %s already %s, skipping.',
                                $member,
                                $event->EmailAddress
                            ),
                            'payload' => $event
                        ];
                        break;
                    }
                    $member->setPrimaryEmail($event->EmailAddress);
                    $this->sendMemberUpdate($member);
                    $this->em->persist($member);
                    $this->em->flush();
                    $output[] = [
                        'result' => sprintf(
                            'Email for %s updated from %s to %s',
                            $member,
                            $event->OldEmailAddress,
                            $event->EmailAddress
                        ),
                        'payload' => $event
                    ];
                    break;
                default:
                    $output[] = [
                        'result' => 'No action taken.',
                        'payload' => $event
                    ];
            }
        }
        return $output;
    }

    public function sendPasswordReset(User $user, ResetPasswordToken $resetToken): void
    {
        $headers = new Headers();
        $headers->addTextHeader('X-Cmail-GroupName', 'Password Reset');
        $headers->addTextHeader('X-MC-Tags', 'Password Reset');
        $email = (new TemplatedEmail($headers))
            ->from(new Address($this->params->get('app.email.from'), $this->params->get('app.name')))
            ->to($user->getEmail())
            ->subject('Your password reset request')
            ->htmlTemplate('reset_password/email.html.twig')
            ->textTemplate('reset_password/email.txt.twig')
            ->context([
                'importance' => false,
                'action_url' => $this->router->generate('app_reset_password', ['token' => $resetToken->getToken()], UrlGeneratorInterface::ABSOLUTE_URL),
                'action_text' => 'Reset My Password',
                'exception' => false,
                'resetToken' => $resetToken
            ])
        ;
        $this->mailer->send($email);
    }

    public function sendMemberEmail(array $formData, Member $member, User $actor): void
    {
        $headers = new Headers();
        $headers->addTextHeader('X-Cmail-GroupName', 'Member Message');
        $headers->addTextHeader('X-MC-Tags', 'Member Message');
        $message = new TemplatedEmail($headers);
        $message
            ->to($member->getPrimaryEmail())
            ->from(new Address($this->params->get('app.email.from'), $this->params->get('app.name')))
            ->replyTo($formData['reply_to'] ? $formData['reply_to'] : $this->params->get('app.email.to'))
            ->subject($formData['subject'])
            ->htmlTemplate('directory/message_email_template.html.twig')
            ->context([
                'subject' => $formData['subject'],
                'body' => $formData['message_body']
            ])
        ;
        if ($formData['send_copy']) {
            $message->bcc($actor->getEmail());
        }
        $this->mailer->send($message);
        $this->communicationLogService->log(
            'Email',
            sprintf(
                "To: %s  \nSubject: %s  \n---  \n%s",
                $member->getPrimaryEmail(),
                $formData['subject'],
                $formData['message_body']
            ),
            $member,
            $actor,
            $formData
        );
    }

    public function sendMemberUpdate(Member $member): void
    {
        $headers = new Headers();
        $headers->addTextHeader('X-Cmail-GroupName', 'Member Record Update');
        $headers->addTextHeader('X-MC-Tags', 'Member Record Update');
        $message = new TemplatedEmail($headers);
        $message
            ->to($this->params->get('app.email.to'))
            ->from($this->params->get('app.email.from'))
            ->subject(sprintf('Member Record Update: %s', $member->getDisplayName()))
            ->htmlTemplate('update/email_update.html.twig')
            ->context(['member' => $member])
            ;
        if ($member->getPrimaryEmail()) {
            $message->replyTo($member->getPrimaryEmail());
        }
        $this->mailer->send($message);
    }

    public function getFromEmailAddress(): string
    {
        return $this->params->get('app.email.from');
    }

    /* Private Methods */

    private function buildCustomFieldArray(Member $member): array
    {
        return [
            [
                'Key' => 'Member Status',
                'Value' => $member->getStatus()->getLabel()
            ],
            [
                'Key' => 'First Name',
                'Value' => $member->getFirstName()
            ],
            [
                'Key' => 'Preferred Name',
                'Value' => $member->getPreferredName()
            ],
            [
                'Key' => 'Middle Name',
                'Value' => $member->getMiddleName()
            ],
            [
                'Key' => 'Last Name',
                'Value' => $member->getLastName()
            ],
            [
                'Key' => 'Class Year',
                'Value' => $member->getClassYear()
            ],
            [
                'Key' => 'Birth Date',
                'Value' => $member->getBirthDate() ? $member->getBirthDate()->format('Y-m-d') : null
            ],
            [
                'Key' => 'Join Date',
                'Value' => $member->getJoinDate() ? $member->getJoinDate()->format('Y-m-d') : null
            ],
            [
                'Key' => 'Local Identifier',
                'Value' => $member->getLocalIdentifier()
            ],
            [
                'Key' => 'External Identifier',
                'Value' => $member->getExternalIdentifier()
            ],
            [
                'Key' => 'Primary Telephone Number',
                'Value' => $member->getPrimaryTelephoneNumber()
            ],
            [
                'Key' => 'Mailing Address Line 1',
                'Value' => $member->getMailingAddressLine1()
            ],
            [
                'Key' => 'Mailing Address Line 2',
                'Value' => $member->getMailingAddressLine2()
            ],
            [
                'Key' => 'Mailing City',
                'Value' => $member->getMailingCity()
            ],
            [
                'Key' => 'Mailing State',
                'Value' => $member->getMailingState()
            ],
            [
                'Key' => 'Mailing Postal Code',
                'Value' => $member->getMailingPostalCode()
            ],
            [
                'Key' => 'Mailing Country',
                'Value' => $member->getMailingCountry()
            ],
            [
                'Key' => 'Employer',
                'Value' => $member->getEmployer()
            ],
            [
                'Key' => 'Job Title',
                'Value' => $member->getJobTitle()
            ],
            [
                'Key' => 'Occupation',
                'Value' => $member->getOccupation()
            ],
            [
                'Key' => 'LinkedIn Profile',
                'Value' => $member->getLinkedinUrl()
            ],
            [
                'Key' => 'Facebook Profile',
                'Value' => $member->getFacebookUrl()
            ],
            [
                'Key' => 'Tags',
                'Value' => $member->getTagsAsCSV()
            ],
            [
                'Key' => 'Update Token',
                'Value' => $member->getUpdateToken()
            ]
        ];
    }
}
