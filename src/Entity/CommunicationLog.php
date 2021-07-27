<?php

namespace App\Entity;

use App\Repository\CommunicationLogRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=CommunicationLogRepository::class)
 * @UniqueEntity({"member","type","loggedAt"})
 * @Gedmo\Loggable
 */
class CommunicationLog
{
    use TimestampableEntity;

    const COMMUNICATION_TYPES = [
        'Email' => 'EMAIL',
        'Text Message' => 'SMS',
        'Telephone Call' => 'TELEPHONE',
        'Direct Message' => 'DM',
        'Postal Mail' => 'MAIL',
        'Other' => 'OTHER'
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Member::class, inversedBy="communicationLogs")
     * @ORM\JoinColumn(nullable=false)
     * @Gedmo\Versioned
     */
    private $member;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Gedmo\Versioned
     */
    private $loggedAt;

    /**
     * @ORM\Column(type="string", length=255)
     * @Gedmo\Versioned
     */
    private $type;

    /**
     * @ORM\Column(type="text")
     * @Gedmo\Versioned
     */
    private $summary;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="communicationLogs")
     */
    private $user;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $payload = [];

    public function __construct()
    {
        $this->loggedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMember(): ?Member
    {
        return $this->member;
    }

    public function setMember(?Member $member): self
    {
        $this->member = $member;

        return $this;
    }

    public function getLoggedAt(): ?\DateTimeInterface
    {
        return $this->loggedAt;
    }

    public function setLoggedAt(\DateTimeInterface $loggedAt): self
    {
        $this->loggedAt = $loggedAt;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getSummary(): ?string
    {
        return $this->summary;
    }

    public function setSummary(string $summary): self
    {
        $this->summary = $summary;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /* Model Methods */
    public function __toString(): string
    {
        return sprintf(
            'Communications Log #%d for %s',
            $this->id,
            $this->member
        );
    }

    public function getTypeDisplay(): string
    {
        $search = array_search($this->type, self::COMMUNICATION_TYPES);
        if ($search) {
            return $search;
        }
        return $this->type;
    }

    public function getPayload(): ?array
    {
        return $this->payload;
    }

    public function setPayload(?array $payload): self
    {
        $this->payload = $payload;

        return $this;
    }

}
