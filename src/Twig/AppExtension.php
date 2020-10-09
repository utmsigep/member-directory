<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Doctrine\ORM\EntityManagerInterface;

use App\Service\EmailService;
use App\Service\PostalValidatorService;
use App\Entity\Tag;
use App\Entity\DirectoryCollection;

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
            new TwigFunction('get_directory_collections', [$this, 'getDirectoryCollections']),
            new TwigFunction('get_tags', [$this, 'getTags']),
            new TwigFunction('is_email_service_configured', [$this, 'isEmailServiceConfigured']),
            new TwigFunction('is_postal_validator_service_configured', [$this, 'isPostalValidatorServiceConfigured']),
            new TwigFunction('gravatar', [$this, 'gravatar']),
        ];
    }

    public function getDirectoryCollections()
    {
        $directoryCollections = $this->entityManager->getRepository(DirectoryCollection::class)->findBy([], ['position' => 'ASC']);
        return $directoryCollections;
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

    public function gravatar(?string $email, int $size = 256): string
    {
        if ($email) {
            return sprintf(
                'https://www.gravatar.com/avatar/%s?size=%d&default=mm',
                md5($email),
                $size
            );
        }
        // Default Gravatar image
        return sprintf(
            'https://www.gravatar.com/avatar/%s?size=%d&default=mm',
            md5('unknown-user@example.com'),
            $size
        );
    }
}
