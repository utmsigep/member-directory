<?php

namespace App\Entity;

use App\Repository\DirectoryCollectionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="Gedmo\Sortable\Entity\Repository\SortableRepository")
 */
class DirectoryCollection
{

    const FILTER_ENUMS = [
        'include',
        'exclude',
        null
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $label;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $icon;

    /**
     * @ORM\ManyToMany(targetEntity=MemberStatus::class, inversedBy="directoryCollections")
     */
    private $memberStatuses;

    /**
     * @ORM\Column(type="boolean")
     */
    private $showMemberStatus;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $groupBy;

    /**
     * @Gedmo\Slug(fields={"label"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $slug;

    /**
     * @Gedmo\SortablePosition
     * @ORM\Column(type="integer", nullable=true)
     */
    private $position;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $filterLost;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $filterLocalDoNotContact;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $filterDeceased;

    public function __construct()
    {
        $this->memberStatuses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    public function getShowMemberStatus(): ?bool
    {
        return $this->showMemberStatus;
    }

    public function setShowMemberStatus(bool $showMemberStatus): self
    {
        $this->showMemberStatus = $showMemberStatus;

        return $this;
    }

    public function getGroupBy(): ?string
    {
        return $this->groupBy;
    }

    public function setGroupBy(string $groupBy): self
    {
        $this->groupBy = $groupBy;

        return $this;
    }

    /**
     * @return Collection|MemberStatus[]
     */
    public function getMemberStatuses(): Collection
    {
        return $this->memberStatuses;
    }

    public function addMemberStatus(MemberStatus $memberStatus): self
    {
        if (!$this->memberStatuses->contains($memberStatus)) {
            $this->memberStatuses[] = $memberStatus;
        }

        return $this;
    }

    public function removeMemberStatus(MemberStatus $memberStatus): self
    {
        if ($this->memberStatuses->contains($memberStatus)) {
            $this->memberStatuses->removeElement($memberStatus);
        }

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): self
    {
        $this->position = $position;
        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function __toString(): string
    {
        return $this->label;
    }

    public function getFilterLost(): ?string
    {
        return $this->filterLost;
    }

    public function setFilterLost(?string $filterLost): self
    {
        $this->filterLost = $filterLost;

        return $this;
    }

    public function getFilterLocalDoNotContact(): ?string
    {
        return $this->filterLocalDoNotContact;
    }

    public function setFilterLocalDoNotContact(?string $filterLocalDoNotContact): self
    {
        $this->filterLocalDoNotContact = $filterLocalDoNotContact;

        return $this;
    }

    public function getFilterDeceased(): ?string
    {
        return $this->filterDeceased;
    }

    public function setFilterDeceased(?string $filterDeceased): self
    {
        $this->filterDeceased = $filterDeceased;

        return $this;
    }
}
