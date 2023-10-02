<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Loggable\Loggable;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table]
#[ORM\Index(columns: ['first_name', 'preferred_name', 'middle_name', 'last_name'], flags: ['fulltext'])]
#[ORM\Entity(repositoryClass: 'App\Repository\MemberRepository')]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity('localIdentifier')]
#[UniqueEntity('externalIdentifier')]
#[UniqueEntity('primaryEmail')]
#[Gedmo\Loggable]
class Member implements Loggable
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['member_main'])]
    private $id;

    #[ORM\Column(type: 'string', nullable: true, length: 255, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^[a-z0-9\-\_]+$/i', htmlPattern: '[a-zA-Z0-9\-\_]+', match: true, message: 'Only alphanumeric characters, dashes and underscores are allowed.')]
    #[Groups(['member_main'])]
    #[Gedmo\Versioned]
    private $localIdentifier;

    #[ORM\Column(type: 'string', nullable: true, length: 255, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^[a-z0-9\-\_]+$/i', htmlPattern: '[a-zA-Z0-9\-\_]+', match: true, message: 'Only alphanumeric characters, dashes and underscores are allowed.')]
    #[Groups(['member_extended'])]
    #[Gedmo\Versioned]
    private $externalIdentifier;

    #[ORM\ManyToOne(targetEntity: MemberStatus::class, inversedBy: 'members')]
    #[Assert\NotBlank]
    #[Groups(['member_main'])]
    #[Gedmo\Versioned]
    private $status;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['member_main'])]
    #[Gedmo\Versioned]
    private $prefix;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\NotBlank]
    #[Groups(['member_main'])]
    #[Gedmo\Versioned]
    private $firstName;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['member_main'])]
    #[Gedmo\Versioned]
    private $preferredName;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['member_main'])]
    #[Gedmo\Versioned]
    private $middleName;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\NotBlank]
    #[Groups(['member_main'])]
    #[Gedmo\Versioned]
    private $lastName;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['member_main'])]
    #[Gedmo\Versioned]
    private $suffix;

    #[ORM\Column(type: 'date', nullable: true)]
    #[Assert\Type('\DateTimeInterface')]
    #[Gedmo\Versioned]
    private $birthDate;

    #[ORM\Column(type: 'date', nullable: true)]
    #[Assert\Type('\DateTimeInterface')]
    #[Gedmo\Versioned]
    private $joinDate;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups(['member_main'])]
    #[Gedmo\Versioned]
    private $classYear;

    #[ORM\Column(type: 'boolean', nullable: true)]
    #[Groups(['member_main'])]
    #[Gedmo\Versioned]
    private $isDeceased = false;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Email]
    #[Groups(['member_main'])]
    #[Gedmo\Versioned]
    private $primaryEmail;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['member_main'])]
    #[Gedmo\Versioned]
    private $primaryTelephoneNumber;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['member_main'])]
    #[Gedmo\Versioned]
    private $mailingAddressLine1;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['member_main'])]
    #[Gedmo\Versioned]
    private $mailingAddressLine2;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['member_main'])]
    #[Gedmo\Versioned]
    private $mailingCity;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['member_main'])]
    #[Gedmo\Versioned]
    private $mailingState;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['member_main'])]
    #[Gedmo\Versioned]
    private $mailingPostalCode;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['member_main'])]
    #[Gedmo\Versioned]
    private $mailingCountry;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 8, nullable: true)]
    #[Assert\Type('numeric')]
    #[Groups(['member_extended'])]
    #[Gedmo\Versioned]
    protected $mailingLatitude;

    #[ORM\Column(type: 'decimal', precision: 11, scale: 8, nullable: true)]
    #[Assert\Type('numeric')]
    #[Groups(['member_extended'])]
    #[Gedmo\Versioned]
    protected $mailingLongitude;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Gedmo\Versioned]
    private $employer;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Gedmo\Versioned]
    private $jobTitle;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Gedmo\Versioned]
    private $occupation;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Regex(pattern: '/^https?\:\/\/(www\.)?facebook.com\/(.*)$/i', htmlPattern: 'https?://(www.)?facebook.com/.+', message: 'Please provide a Facebook URL')]
    #[Groups(['member_main'])]
    #[Gedmo\Versioned]
    private $facebookUrl;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Regex(pattern: '/^https?\:\/\/(www\.)?linkedin.com\/(.*)$/i', htmlPattern: 'https?://(www.)?linkedin.com/.+', message: 'Please provide a LinkedIn URL')]
    #[Groups(['member_main'])]
    #[Gedmo\Versioned]
    private $linkedinUrl;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['member_main'])]
    #[Gedmo\Versioned]
    private $photoUrl;

    #[ORM\Column(type: 'boolean', nullable: true)]
    #[Groups(['member_main'])]
    #[Gedmo\Versioned]
    private $isLost = false;

    #[ORM\Column(type: 'boolean', nullable: true)]
    #[Groups(['member_main'])]
    #[Gedmo\Versioned]
    private $isLocalDoNotContact = false;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Gedmo\Versioned]
    private $directoryNotes;

    #[ORM\ManyToMany(targetEntity: Tag::class, mappedBy: 'members')]
    #[ORM\OrderBy(['tagName' => 'ASC'])]
    #[Groups(['member_extended'])]
    private $tags;

    #[ORM\OneToMany(targetEntity: Donation::class, mappedBy: 'member')]
    #[ORM\OrderBy(['receivedAt' => 'DESC'])]
    private $donations;

    #[ORM\OneToMany(targetEntity: CommunicationLog::class, mappedBy: 'member', orphanRemoval: true)]
    #[ORM\OrderBy(['loggedAt' => 'DESC'])]
    private $communicationLogs;

    #[ORM\ManyToMany(targetEntity: Event::class, mappedBy: 'attendees')]
    private $events;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
        $this->donations = new ArrayCollection();
        $this->communicationLogs = new ArrayCollection();
        $this->events = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLocalIdentifier(): ?string
    {
        return $this->localIdentifier;
    }

    public function setLocalIdentifier(?string $localIdentifier): self
    {
        $this->localIdentifier = $localIdentifier;

        return $this;
    }

    public function getExternalIdentifier(): ?string
    {
        return $this->externalIdentifier;
    }

    public function setExternalIdentifier(?string $externalIdentifier): self
    {
        $this->externalIdentifier = $externalIdentifier;

        return $this;
    }

    public function getStatus(): ?MemberStatus
    {
        return $this->status;
    }

    public function setStatus(?MemberStatus $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getPrefix(): ?string
    {
        return $this->prefix;
    }

    public function setPrefix(?string $prefix): self
    {
        $this->prefix = $prefix;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getPreferredName(): ?string
    {
        return $this->preferredName;
    }

    public function setPreferredName(?string $preferredName): self
    {
        $this->preferredName = $preferredName;

        return $this;
    }

    public function getMiddleName(): ?string
    {
        return $this->middleName;
    }

    public function setMiddleName(?string $middleName): self
    {
        $this->middleName = $middleName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getSuffix(): ?string
    {
        return $this->suffix;
    }

    public function setSuffix(?string $suffix): self
    {
        $this->suffix = $suffix;

        return $this;
    }

    public function getBirthDate(): ?\DateTimeInterface
    {
        return $this->birthDate;
    }

    public function setBirthDate(?\DateTimeInterface $birthDate): self
    {
        if ($this->assertEqualDates($this->birthDate, $birthDate)) {
            return $this;
        }

        $this->birthDate = $birthDate;

        return $this;
    }

    public function getJoinDate(): ?\DateTimeInterface
    {
        return $this->joinDate;
    }

    public function setJoinDate(?\DateTimeInterface $joinDate): self
    {
        if ($this->assertEqualDates($this->joinDate, $joinDate)) {
            return $this;
        }

        $this->joinDate = $joinDate;

        return $this;
    }

    public function getClassYear(): ?int
    {
        return $this->classYear;
    }

    public function setClassYear(?int $classYear): self
    {
        $this->classYear = $classYear;

        return $this;
    }

    public function getIsDeceased(): ?bool
    {
        return $this->isDeceased;
    }

    public function setIsDeceased(?bool $isDeceased): self
    {
        $this->isDeceased = $isDeceased;

        return $this;
    }

    public function getPrimaryEmail(): ?string
    {
        return $this->primaryEmail;
    }

    public function setPrimaryEmail(?string $primaryEmail): self
    {
        $primaryEmail = $this->formatEmail($primaryEmail);
        $this->primaryEmail = $primaryEmail;

        return $this;
    }

    public function getPrimaryTelephoneNumber(): ?string
    {
        return $this->primaryTelephoneNumber;
    }

    public function setPrimaryTelephoneNumber(?string $primaryTelephoneNumber): self
    {
        $primaryTelephoneNumber = $this->formatTelephoneNumber($primaryTelephoneNumber);
        $this->primaryTelephoneNumber = $primaryTelephoneNumber;

        return $this;
    }

    public function getMailingAddressLine1(): ?string
    {
        return $this->mailingAddressLine1;
    }

    public function setMailingAddressLine1(?string $mailingAddressLine1): self
    {
        $this->mailingAddressLine1 = $mailingAddressLine1;

        return $this;
    }

    public function getMailingAddressLine2(): ?string
    {
        return $this->mailingAddressLine2;
    }

    public function setMailingAddressLine2(?string $mailingAddressLine2): self
    {
        $this->mailingAddressLine2 = $mailingAddressLine2;

        return $this;
    }

    public function getMailingCity(): ?string
    {
        return $this->mailingCity;
    }

    public function setMailingCity(?string $mailingCity): self
    {
        $this->mailingCity = $mailingCity;

        return $this;
    }

    public function getMailingState(): ?string
    {
        return $this->mailingState;
    }

    public function setMailingState(?string $mailingState): self
    {
        $this->mailingState = $mailingState;

        return $this;
    }

    public function getMailingPostalCode(): ?string
    {
        return $this->mailingPostalCode;
    }

    public function setMailingPostalCode(?string $mailingPostalCode): self
    {
        $this->mailingPostalCode = $mailingPostalCode;

        return $this;
    }

    public function getMailingCountry(): ?string
    {
        return $this->mailingCountry;
    }

    public function setMailingCountry(?string $mailingCountry): self
    {
        $this->mailingCountry = $mailingCountry;

        return $this;
    }

    public function getMailingLatitude(): ?float
    {
        return (float) $this->mailingLatitude;
    }

    public function setMailingLatitude(?float $mailingLatitude): self
    {
        $this->mailingLatitude = $mailingLatitude;

        return $this;
    }

    public function getMailingLongitude(): ?float
    {
        return (float) $this->mailingLongitude;
    }

    public function setMailingLongitude(?float $mailingLongitude): self
    {
        $this->mailingLongitude = $mailingLongitude;

        return $this;
    }

    public function getEmployer(): ?string
    {
        return $this->employer;
    }

    public function setEmployer(?string $employer): self
    {
        $this->employer = $employer;

        return $this;
    }

    public function getJobTitle(): ?string
    {
        return $this->jobTitle;
    }

    public function setJobTitle(?string $jobTitle): self
    {
        $this->jobTitle = $jobTitle;

        return $this;
    }

    public function getOccupation(): ?string
    {
        return $this->occupation;
    }

    public function setOccupation(?string $occupation): self
    {
        $this->occupation = $occupation;

        return $this;
    }

    public function getFacebookUrl(): ?string
    {
        return $this->facebookUrl;
    }

    public function setFacebookUrl(?string $facebookUrl): self
    {
        $this->facebookUrl = $facebookUrl;

        return $this;
    }

    public function getLinkedinUrl(): ?string
    {
        return $this->linkedinUrl;
    }

    public function setLinkedinUrl(?string $linkedinUrl): self
    {
        $this->linkedinUrl = $linkedinUrl;

        return $this;
    }

    public function getDirectoryNotes(): ?string
    {
        return $this->directoryNotes;
    }

    public function setDirectoryNotes(?string $directoryNotes): self
    {
        $this->directoryNotes = $directoryNotes;

        return $this;
    }

    public function getPhotoUrl(int $size = 400): ?string
    {
        return $this->photoUrl;
    }

    public function setPhotoUrl(?string $photoUrl): ?string
    {
        $this->photoUrl = $photoUrl;

        return $this;
    }

    public function getIsLost(): ?bool
    {
        return $this->isLost;
    }

    public function setIsLost(?bool $isLost): self
    {
        $this->isLost = $isLost;

        return $this;
    }

    public function getIsLocalDoNotContact(): ?bool
    {
        return $this->isLocalDoNotContact;
    }

    public function setIsLocalDoNotContact(?bool $isLocalDoNotContact): self
    {
        $this->isLocalDoNotContact = $isLocalDoNotContact;

        return $this;
    }

    /**
     * @return Collection|Tag[]
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags[] = $tag;
            $tag->addMember($this);
        }

        return $this;
    }

    public function removeTag(Tag $tag): self
    {
        if ($this->tags->contains($tag)) {
            $this->tags->removeElement($tag);
            $tag->removeMember($this);
        }

        return $this;
    }

    /**
     * @return Collection|Donation[]
     */
    public function getDonations(): Collection
    {
        return $this->donations;
    }

    public function addDonation(Donation $donation): self
    {
        if (!$this->donations->contains($donation)) {
            $this->donations[] = $donation;
            $donation->setMember($this);
        }

        return $this;
    }

    public function removeDonation(Donation $donation): self
    {
        if ($this->donations->contains($donation)) {
            $this->donations->removeElement($donation);
            // set the owning side to null (unless already changed)
            if ($donation->getMember() === $this) {
                $donation->setMember(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|CommunicationLog[]
     */
    public function getCommunicationLogs(): Collection
    {
        return $this->communicationLogs;
    }

    public function addCommunicationLog(CommunicationLog $communicationLog): self
    {
        if (!$this->communicationLogs->contains($communicationLog)) {
            $this->communicationLogs[] = $communicationLog;
            $communicationLog->setMember($this);
        }

        return $this;
    }

    public function removeCommunicationLog(CommunicationLog $communicationLog): self
    {
        if ($this->communicationLogs->removeElement($communicationLog)) {
            // set the owning side to null (unless already changed)
            if ($communicationLog->getMember() === $this) {
                $communicationLog->setMember(null);
            }
        }

        return $this;
    }

    /**
     * Model Methods.
     */
    public function __toString(): string
    {
        return sprintf('%s, %s (%s)', $this->lastName, $this->preferredName, $this->localIdentifier);
    }

    #[Groups(['member_main'])]
    public function getDisplayName(): string
    {
        return $this->preferredName.' '.$this->lastName;
    }

    public function getTagsAsCSV(): string
    {
        $output = [];
        foreach ($this->tags as $tag) {
            $output[] = $tag->getTagName();
        }

        return join(',', $output);
    }

    public function getUpdateToken(): string
    {
        return sha1(json_encode([
            $this->id,
            $this->externalIdentifier,
            $this->updatedAt,
        ]));
    }

    public function formatMemberMessage(string $content): string
    {
        $content = preg_replace('/\[FirstName\]/i', $this->getFirstName(), $content);
        $content = preg_replace('/\[MiddleName\]/i', $this->getMiddleName(), $content);
        $content = preg_replace('/\[PreferredName\]/i', $this->getPreferredName(), $content);
        $content = preg_replace('/\[LastName\]/i', $this->getLastName(), $content);
        $content = preg_replace('/\[MailingAddressLine1\]/i', $this->getMailingAddressLine1(), $content);
        $content = preg_replace('/\[MailingAddressLine2\]/i', $this->getMailingAddressLine2(), $content);
        $content = preg_replace('/\[MailingCity\]/i', $this->getMailingCity(), $content);
        $content = preg_replace('/\[MailingState\]/i', $this->getMailingState(), $content);
        $content = preg_replace('/\[MailingPostalCode\]/i', $this->getMailingPostalCode(), $content);
        $content = preg_replace('/\[MailingCountry\]/i', $this->getMailingCountry(), $content);
        $content = preg_replace('/\[PrimaryEmail\]/i', $this->getPrimaryEmail(), $content);
        $content = preg_replace('/\[PrimaryTelephoneNumber\]/i', $this->getPrimaryTelephoneNumber(), $content);

        return $content;
    }

    #[ORM\PreFlush]
    public function updateFieldsIfBlank()
    {
        // Ensure preferred name is set to first name if left blank
        if (!$this->preferredName) {
            $this->preferredName = $this->firstName;
        }
        // Ensure mailing country is set if mailingAddressLine1 is set
        if (!$this->mailingCountry && $this->mailingAddressLine1) {
            $this->mailingCountry = 'United States';
        }
    }

    /**
     * Private Methods.
     */
    private function assertEqualDates($date1, $date2): bool
    {
        // Assert that two \DateTime instances are the same
        if (
            $date1
            && $date2
            && method_exists($date1, 'format')
            && method_exists($date2, 'format')
            && $date1->format('Y-m-d') === $date2->format('Y-m-d')
        ) {
            return true;
        }

        return false;
    }

    private function formatTelephoneNumber(?string $telephoneNumber): string
    {
        return $telephoneNumber = preg_replace(
            '/.*(\d{3})[^\d]{0,7}(\d{3})[^\d]{0,7}(\d{4}).*/',
            '($1) $2-$3',
            $telephoneNumber
        );
    }

    private function formatEmail(?string $email): string
    {
        return trim(mb_strtolower($email));
    }

    /**
     * @return Collection|Event[]
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): self
    {
        if (!$this->events->contains($event)) {
            $this->events[] = $event;
            $event->addAttendee($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): self
    {
        if ($this->events->removeElement($event)) {
            $event->removeAttendee($this);
        }

        return $this;
    }
}
