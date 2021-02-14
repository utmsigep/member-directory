<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MemberStatusRepository")
 * @Gedmo\Loggable
 * @UniqueEntity("code")
 */
class MemberStatus
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
     * @ORM\Column(type="string", length=255)
     * @Gedmo\Versioned
     * @Groups({"status_main"})
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=255)
     * @Gedmo\Versioned
     * @Groups({"status_main"})
     */
    private $label;

    /**
     * @ORM\OneToMany(targetEntity=Member::class, mappedBy="status")
     * @ORM\OrderBy({"lastName": "ASC"})
     */
    private $members;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isInactive = false;

    /**
     * @ORM\ManyToMany(targetEntity=DirectoryCollection::class, mappedBy="memberStatuses")
     */
    private $directoryCollections;

    public function __construct()
    {
        $this->members = new ArrayCollection();
        $this->directoryCollections = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $code = trim($code);
        $code = str_replace(' ', '_', $code);
        $code = preg_replace("/[^A-Za-z0-9_]/", '', $code);
        $code = mb_strtoupper($code);
        $this->code = $code;
        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return Collection|Member[]
     */
    public function getMembers(): Collection
    {
        return $this->members;
    }

    public function addMember(Member $member): self
    {
        if (!$this->members->contains($member)) {
            $this->members[] = $member;
            $member->setStatus($this);
        }

        return $this;
    }

    public function removeMember(Member $member): self
    {
        if ($this->members->contains($member)) {
            $this->members->removeElement($member);
            // set the owning side to null (unless already changed)
            if ($member->getStatus() === $this) {
                $member->setStatus(null);
            }
        }

        return $this;
    }

    public function setIsInactive(bool $isInactive): self
    {
        $this->isInactive = $isInactive;
        return $this;
    }

    public function getIsInactive(): ?bool
    {
        return $this->isInactive;
    }

    /**
     * @return Collection|DirectoryCollection[]
     */
    public function getDirectoryCollections(): Collection
    {
        return $this->directoryCollections;
    }

    public function addDirectoryCollection(DirectoryCollection $directoryCollection): self
    {
        if (!$this->directoryCollections->contains($directoryCollection)) {
            $this->directoryCollections[] = $directoryCollection;
            $directoryCollection->addMemberStatus($this);
        }

        return $this;
    }

    public function removeDirectoryCollection(DirectoryCollection $directoryCollection): self
    {
        if ($this->directoryCollections->contains($directoryCollection)) {
            $this->directoryCollections->removeElement($directoryCollection);
            $directoryCollection->removeMemberStatus($this);
        }

        return $this;
    }

    /* Model Methods */

    public function __toString(): string
    {
        return $this->label;
    }
}
