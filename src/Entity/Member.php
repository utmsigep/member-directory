<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MemberRepository")
 */
class Member
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", unique=true, length=255)
     */
    private $localIdentifier;

    /**
     * @ORM\Column(type="string", unique=true, length=255)
     */
    private $externalIdentifier;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\MemberStatus", inversedBy="members")
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $preferredName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $middleName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $lastName;

    /**
     * @ORM\Column(type="date")
     */
    private $joinDate;

    /**
     * @ORM\Column(type="integer")
     */
    private $classYear;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isDeceased;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $employer;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $jobTitle;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $occupation;

    /**
     * @ORM\Column(type="text")
     */
    private $notes;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\MemberAddress", mappedBy="member")
     */
    private $memberAddresses;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\MemberEmail", mappedBy="member")
     */
    private $memberEmails;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\MemberLink", mappedBy="member")
     */
    private $memberLinks;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\MemberPhoneNumber", mappedBy="member")
     */
    private $memberPhoneNumbers;

    public function __construct()
    {
        $this->memberAddresses = new ArrayCollection();
        $this->memberEmails = new ArrayCollection();
        $this->memberLinks = new ArrayCollection();
        $this->memberPhoneNumbers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLocalIdentifier(): ?string
    {
        return $this->localIdentifier;
    }

    public function setLocalIdentifier(string $localIdentifier): self
    {
        $this->localIdentifier = $localIdentifier;

        return $this;
    }

    public function getExternalIdentifier(): ?string
    {
        return $this->externalIdentifier;
    }

    public function setExternalIdentifier(string $externalIdentifier): self
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

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getPreferredName(): ?string
    {
        return $this->preferredName;
    }

    public function setPreferredName(string $preferredName): self
    {
        $this->preferredName = $preferredName;

        return $this;
    }

    public function getMiddleName(): ?string
    {
        return $this->middleName;
    }

    public function setMiddleName(string $middleName): self
    {
        $this->middleName = $middleName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
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

    public function setClassYear(int $classYear): self
    {
        $this->classYear = $classYear;

        return $this;
    }

    public function getIsDeceased(): ?bool
    {
        return $this->isDeceased;
    }

    public function setIsDeceased(bool $isDeceased): self
    {
        $this->isDeceased = $isDeceased;

        return $this;
    }

    public function getEmployer(): ?string
    {
        return $this->employer;
    }

    public function setEmployer(string $employer): self
    {
        $this->employer = $employer;

        return $this;
    }

    public function getJobTitle(): ?string
    {
        return $this->jobTitle;
    }

    public function setJobTitle(string $jobTitle): self
    {
        $this->jobTitle = $jobTitle;

        return $this;
    }

    public function getOccupation(): ?string
    {
        return $this->occupation;
    }

    public function setOccupation(string $occupation): self
    {
        $this->occupation = $occupation;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(string $notes): self
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * @return Collection|MemberAddress[]
     */
    public function getMemberAddresses(): Collection
    {
        return $this->memberAddresses;
    }

    public function addMemberAddress(MemberAddress $memberAddress): self
    {
        if (!$this->memberAddresses->contains($memberAddress)) {
            $this->memberAddresses[] = $memberAddress;
            $memberAddress->setMember($this);
        }

        return $this;
    }

    public function removeMemberAddress(MemberAddress $memberAddress): self
    {
        if ($this->memberAddresses->contains($memberAddress)) {
            $this->memberAddresses->removeElement($memberAddress);
            // set the owning side to null (unless already changed)
            if ($memberAddress->getMember() === $this) {
                $memberAddress->setMember(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|MemberEmail[]
     */
    public function getMemberEmails(): Collection
    {
        return $this->memberEmails;
    }

    public function addMemberEmail(MemberEmail $memberEmail): self
    {
        if (!$this->memberEmails->contains($memberEmail)) {
            $this->memberEmails[] = $memberEmail;
            $memberEmail->setMember($this);
        }

        return $this;
    }

    public function removeMemberEmail(MemberEmail $memberEmail): self
    {
        if ($this->memberEmails->contains($memberEmail)) {
            $this->memberEmails->removeElement($memberEmail);
            // set the owning side to null (unless already changed)
            if ($memberEmail->getMember() === $this) {
                $memberEmail->setMember(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|MemberLink[]
     */
    public function getMemberLinks(): Collection
    {
        return $this->memberLinks;
    }

    public function addMemberLink(MemberLink $memberLink): self
    {
        if (!$this->memberLinks->contains($memberLink)) {
            $this->memberLinks[] = $memberLink;
            $memberLink->setMember($this);
        }

        return $this;
    }

    public function removeMemberLink(MemberLink $memberLink): self
    {
        if ($this->memberLinks->contains($memberLink)) {
            $this->memberLinks->removeElement($memberLink);
            // set the owning side to null (unless already changed)
            if ($memberLink->getMember() === $this) {
                $memberLink->setMember(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|MemberPhoneNumber[]
     */
    public function getMemberPhoneNumbers(): Collection
    {
        return $this->memberPhoneNumbers;
    }

    public function addMemberPhoneNumber(MemberPhoneNumber $memberPhoneNumber): self
    {
        if (!$this->memberPhoneNumbers->contains($memberPhoneNumber)) {
            $this->memberPhoneNumbers[] = $memberPhoneNumber;
            $memberPhoneNumber->setMember($this);
        }

        return $this;
    }

    public function removeMemberPhoneNumber(MemberPhoneNumber $memberPhoneNumber): self
    {
        if ($this->memberPhoneNumbers->contains($memberPhoneNumber)) {
            $this->memberPhoneNumbers->removeElement($memberPhoneNumber);
            // set the owning side to null (unless already changed)
            if ($memberPhoneNumber->getMember() === $this) {
                $memberPhoneNumber->setMember(null);
            }
        }

        return $this;
    }
}
