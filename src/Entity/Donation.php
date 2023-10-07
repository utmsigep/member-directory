<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: 'App\Repository\DonationRepository')]
#[ORM\HasLifecycleCallbacks]
#[Gedmo\Loggable]
class Donation
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Member::class, inversedBy: 'donations')]
    #[Gedmo\Versioned]
    private $member;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Gedmo\Versioned]
    private $donorFirstName;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Gedmo\Versioned]
    private $donorLastName;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Gedmo\Versioned]
    private $receiptIdentifier;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Gedmo\Versioned]
    private $receivedAt;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Gedmo\Versioned]
    private $campaign;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Gedmo\Versioned]
    private $description;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    #[Gedmo\Versioned]
    private $amount;

    #[ORM\Column(type: 'string', length: 255)]
    #[Gedmo\Versioned]
    private $currency = 'USD';

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    #[Gedmo\Versioned]
    private $processingFee;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    #[Gedmo\Versioned]
    private $netAmount;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Gedmo\Versioned]
    private $donorComment;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Gedmo\Versioned]
    private $internalNotes;

    #[Gedmo\Versioned]
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $donationType;

    #[Gedmo\Versioned]
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $cardType;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Gedmo\Versioned]
    private $lastFour;

    #[ORM\Column(type: 'boolean')]
    #[Gedmo\Versioned]
    private $isAnonymous;

    #[Gedmo\Versioned]
    #[ORM\Column(type: 'boolean')]
    private $isRecurring;

    #[ORM\Column(type: 'json')]
    #[Gedmo\Versioned]
    private $transactionPayload = [];

    public function __construct()
    {
        $this->receivedAt = new \DateTimeImmutable();
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

    public function getDonorFirstName(): ?string
    {
        return $this->donorFirstName;
    }

    public function setDonorFirstName(?string $donorFirstName): self
    {
        $this->donorFirstName = $donorFirstName;

        return $this;
    }

    public function getDonorLastName(): ?string
    {
        return $this->donorLastName;
    }

    public function setDonorLastName(?string $donorLastName): self
    {
        $this->donorLastName = $donorLastName;

        return $this;
    }

    public function getReceiptIdentifier(): ?string
    {
        return $this->receiptIdentifier;
    }

    public function setReceiptIdentifier(?string $receiptIdentifier): self
    {
        $this->receiptIdentifier = $receiptIdentifier;

        return $this;
    }

    public function getReceivedAt(): ?\DateTimeInterface
    {
        return $this->receivedAt;
    }

    public function setReceivedAt(\DateTimeInterface $receivedAt): self
    {
        $this->receivedAt = $receivedAt;

        return $this;
    }

    public function getCampaign(): ?string
    {
        return $this->campaign;
    }

    public function setCampaign(string $campaign): self
    {
        $this->campaign = $campaign;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function getProcessingFee(): ?string
    {
        return $this->processingFee;
    }

    public function setProcessingFee(string $processingFee): self
    {
        $this->processingFee = $processingFee;

        return $this;
    }

    public function getNetAmount(): ?string
    {
        return $this->netAmount;
    }

    public function setNetAmount(string $netAmount): self
    {
        $this->netAmount = $netAmount;

        return $this;
    }

    public function getDonorComment(): ?string
    {
        return $this->donorComment;
    }

    public function setDonorComment(?string $donorComment): self
    {
        $this->donorComment = $donorComment;

        return $this;
    }

    public function getInternalNotes(): ?string
    {
        return $this->internalNotes;
    }

    public function setInternalNotes(?string $internalNotes): self
    {
        $this->internalNotes = $internalNotes;

        return $this;
    }

    public function getDonationType(): ?string
    {
        return $this->donationType;
    }

    public function setDonationType(string $donationType): self
    {
        $this->donationType = $donationType;

        return $this;
    }

    public function getCardType(): ?string
    {
        return $this->cardType;
    }

    public function setCardType(?string $cardType): self
    {
        $this->cardType = $cardType;

        return $this;
    }

    public function getLastFour(): ?string
    {
        return $this->lastFour;
    }

    public function setLastFour(?string $lastFour): self
    {
        $this->lastFour = $lastFour;

        return $this;
    }

    public function getIsAnonymous(): ?bool
    {
        return $this->isAnonymous;
    }

    public function setIsAnonymous(bool $isAnonymous): self
    {
        $this->isAnonymous = $isAnonymous;

        return $this;
    }

    public function getIsRecurring(): ?bool
    {
        return $this->isRecurring;
    }

    public function setIsRecurring(bool $isRecurring): self
    {
        $this->isRecurring = $isRecurring;

        return $this;
    }

    public function getTransactionPayload(): ?array
    {
        return $this->transactionPayload;
    }

    public function setTransactionPayload(array $transactionPayload): self
    {
        $this->transactionPayload = $transactionPayload;

        return $this;
    }

    /**
     * Model Methods.
     */
    public function __toString(): string
    {
        $donorName = (string) $this->member;
        if (!$donorName) {
            $donorName = sprintf('%s %s', $this->donorFirstName, $this->donorLastName);
        }

        return sprintf('#%s - %s @ %s (%s %s)', $this->receiptIdentifier, $donorName, $this->receivedAt->format('Y-m-d'), $this->amount, $this->currency);
    }

    #[ORM\PreFlush]
    public function updateFieldsIfBlank()
    {
        if ($this->getMember()) {
            $this->setDonorFirstName($this->getMember()->getFirstName());
            $this->setDonorLastName($this->getMember()->getLastName());
        }
        if (!$this->getMember() && !$this->getDonorFirstName() && !$this->getDonorLastName()) {
            $this->setIsAnonymous(true);
        }
    }
}
