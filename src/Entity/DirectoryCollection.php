<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="Gedmo\Sortable\Entity\Repository\SortableRepository")
 * @Gedmo\Loggable
 */
class DirectoryCollection
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Gedmo\Versioned
     */
    private $label;

    /**
     * @ORM\Column(type="string", length=255)
     * @Gedmo\Versioned
     */
    private $icon = 'fas fa-address-book';

    /**
     * @ORM\ManyToMany(targetEntity=MemberStatus::class, inversedBy="directoryCollections")
     */
    private $memberStatuses;

    /**
     * @ORM\Column(type="boolean")
     * @Gedmo\Versioned
     */
    private $showMemberStatus;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Versioned
     */
    private $groupBy;

    /**
     * @Gedmo\Slug(fields={"label"})
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Versioned
     */
    private $slug;

    /**
     * @Gedmo\SortablePosition
     * @ORM\Column(type="integer", nullable=true)
     * @Gedmo\Versioned
     */
    private $position;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Versioned
     */
    private $filterLost;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Versioned
     */
    private $filterLocalDoNotContact;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Versioned
     */
    private $filterDeceased;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

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
        // Append 'Solid' style if no icon style set
        if (!preg_match('/^fa[srldb]\s/', $this->icon)) {
            return 'fas '.$this->icon;
        }

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

    public function setSlug(?string $slug): self
    {
        $this->slug = $slug;

        return $this;
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

    public function __toString(): string
    {
        return $this->label;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }
}
