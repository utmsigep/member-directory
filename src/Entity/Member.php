<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MemberRepository")
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\Loggable
 */
class Member
{
    /**
     * Hook timestampable behavior
     * updates createdAt, updatedAt fields
     */
    use TimestampableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", nullable=true, length=255)
     * @Assert\NotBlank
     * @Gedmo\Versioned
     */
    private $localIdentifier;

    /**
     * @ORM\Column(type="string", nullable=true, length=255)
     * @Assert\NotBlank
     * @Gedmo\Versioned
     */
    private $externalIdentifier;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\MemberStatus", inversedBy="members")
     * @Assert\NotBlank
     * @Gedmo\Versioned
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotBlank
     * @Gedmo\Versioned
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Versioned
     */
    private $preferredName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Versioned
     */
    private $middleName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotBlank
     * @Gedmo\Versioned
     */
    private $lastName;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Assert\Date
     */
    private $joinDate;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Gedmo\Versioned
     */
    private $classYear;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Gedmo\Versioned
     */
    private $isDeceased = false;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Email
     * @Gedmo\Versioned
     */
    private $primaryEmail;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Versioned
     */
    private $primaryTelephoneNumber;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Versioned
     */
    private $mailingAddressLine1;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Versioned
     */
    private $mailingAddressLine2;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Versioned
     */
    private $mailingCity;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Versioned
     */
    private $mailingState;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Versioned
     */
    private $mailingPostalCode;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Versioned
     */
    private $mailingCountry;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=8, nullable=true)
     * @Gedmo\Versioned
     */
    protected $mailingLatitude;

    /**
     * @ORM\Column(type="decimal", precision=11, scale=8, nullable=true)
     * @Gedmo\Versioned
     */
    protected $mailingLongitude;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Versioned
     */
    private $employer;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Versioned
     */
    private $jobTitle;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Versioned
     */
    private $occupation;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Gedmo\Versioned
     */
    private $facebookIdentifier;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Gedmo\Versioned
     */
    private $isLost = false;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Gedmo\Versioned
     */
    private $isLocalDoNotContact = false;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Gedmo\Versioned
     */
    private $isExternalDoNotContact = false;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $directoryNotes;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Tag", mappedBy="members")
     * @ORM\OrderBy({"tagName": "ASC"})
     */
    private $tags;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
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

    public function getJoinDate(): ?\DateTimeInterface
    {
        return $this->joinDate;
    }

    public function setJoinDate(\DateTimeInterface $joinDate): self
    {
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

    public function setMailingLatitude(?string $mailingLatitude): self
    {
        $this->mailingLatitude = $mailingLatitude;

        return $this;
    }

    public function getMailingLongitude(): ?float
    {
        return (float) $this->mailingLongitude;
    }

    public function setMailingLongitude(?string $mailingLongitude): self
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

    public function getFacebookIdentifier(): ?int
    {
        return $this->facebookIdentifier;
    }

    public function setFacebookIdentifier(?int $facebookIdentifier): self
    {
        $this->facebookIdentifier = $facebookIdentifier;

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

    public function getIsExternalDoNotContact(): ?bool
    {
        return $this->isExternalDoNotContact;
    }

    public function setIsExternalDoNotContact(?bool $isExternalDoNotContact): self
    {
        $this->isExternalDoNotContact = $isExternalDoNotContact;

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
     * Model Methods
     */

    public function __toString(): string
    {
        return sprintf('%s, %s (%s)', $this->lastName, $this->firstName, $this->localIdentifier);
    }

    public function getLocalIdentifierShort(): string
    {
        return (int) preg_replace('/^\d+\-/', '', $this->localIdentifier);
    }

    public function getPhotoUrl(): ?string
    {
        if ($this->facebookIdentifier) {
            return sprintf('https://graph.facebook.com/v3.3/%d/picture?width=400', $this->facebookIdentifier);
        }
        if ($this->primaryEmail) {
            return sprintf('https://www.gravatar.com/avatar/%s?size=400&default=mm', md5($this->primaryEmail));
        }
        // Default Gravatar image
        return 'https://www.gravatar.com/avatar/3c2eb7b3dd3134bd26afcd43c5941ae1?size=400&default=mm';
    }

    /**
     * Event Listeners
     */

     /**
      * @ORM\PreFlush
      */
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
     * Private Methods
     */

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

}
