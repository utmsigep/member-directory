<?php

namespace App\Twig;

use App\Entity\DirectoryCollection;
use App\Entity\Tag;
use App\Entity\User;
use App\Service\EmailService;
use App\Service\PostalValidatorService;
use App\Service\SmsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    protected $entityManager;

    protected $roleHierarchy;

    protected $postalValidatorService;

    protected $emailService;

    protected $smsService;

    public function __construct(
        EntityManagerInterface $entityManager,
        RoleHierarchyInterface $roleHierarchy,
        PostalValidatorService $postalValidatorService,
        EmailService $emailService,
        SmsService $smsService
    ) {
        $this->entityManager = $entityManager;
        $this->roleHierarchy = $roleHierarchy;
        $this->postalValidatorService = $postalValidatorService;
        $this->emailService = $emailService;
        $this->smsService = $smsService;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_all_roles', [$this, 'getAllRolesForUser']),
            new TwigFunction('get_directory_collections', [$this, 'getDirectoryCollections']),
            new TwigFunction('get_tags', [$this, 'getTags']),
            new TwigFunction('is_email_service_configured', [$this, 'isEmailServiceConfigured']),
            new TwigFunction('is_sms_service_configured', [$this, 'isSmsServiceConfigured']),
            new TwigFunction('is_postal_validator_service_configured', [$this, 'isPostalValidatorServiceConfigured']),
            new TwigFunction('gravatar', [$this, 'gravatar']),
        ];
    }

    public function getAllRolesForUser(User $user): array
    {
        $reachableRoles = $this->roleHierarchy->getReachableRoleNames($user->getRoles());
        if (!empty($reachableRoles)) {
            return $reachableRoles;
        }

        return [];
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

    public function isSmsServiceConfigured(): bool
    {
        return $this->smsService->isConfigured();
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
