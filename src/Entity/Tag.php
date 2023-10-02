<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: 'App\Repository\TagRepository')]
#[Gedmo\Loggable]
class Tag
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['tag_main'])]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['tag_main'])]
    #[Gedmo\Versioned]
    private $tagName;

    #[ORM\ManyToMany(targetEntity: Member::class, inversedBy: 'tags')]
    #[ORM\OrderBy(['lastName' => 'ASC', 'firstName' => 'ASC'])]
    private $members;

    public function __construct()
    {
        $this->members = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTagName(): ?string
    {
        return $this->tagName;
    }

    public function setTagName(string $tagName): self
    {
        $this->tagName = $tagName;

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
        }

        return $this;
    }

    public function removeMember(Member $member): self
    {
        if ($this->members->contains($member)) {
            $this->members->removeElement($member);
        }

        return $this;
    }

    /**
     * Model Methods.
     */
    public function __toString(): string
    {
        return $this->tagName;
    }
}
