<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Doctrine\ORM\EntityManagerInterface;

use App\Service\EmailService;
use App\Service\PostalValidatorService;
use App\Entity\Tag;

class AppExtension extends AbstractExtension
{
    protected $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager,
        PostalValidatorService $postalValidatorService,
        EmailService $emailService
    ) {
        $this->entityManager = $entityManager;
        $this->postalValidatorService = $postalValidatorService;
        $this->emailService = $emailService;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_tags', [$this, 'getTags']),
            new TwigFunction('is_email_service_configured', [$this, 'isEmailServiceConfigured']),
            new TwigFunction('is_postal_validator_service_configured', [$this, 'isPostalValidatorServiceConfigured']),
        ];
    }

    public function getTags()
    {
        $tags = $this->entityManager->getRepository(Tag::class)->findBy([], ['tagName' => 'ASC']);
        return $tags;
    }

    public function isEmailServiceConfigured(): bool
    {
        return $this->emailService->isConfigured();
    }

    public function isPostalValidatorServiceConfigured(): bool
    {
        return $this->postalValidatorService->isConfigured();
    }
}
