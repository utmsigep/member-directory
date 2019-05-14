<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Doctrine\ORM\EntityManagerInterface;

use App\Entity\Tag;

class AppExtension extends AbstractExtension
{
    protected $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_tags', [$this, 'getTags']),
        ];
    }

    public function getTags()
    {
        $tags = $this->entityManager->getRepository(Tag::class)->findAll();
        return $tags;
    }
}
