<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Scheb\TwoFactorBundle\Model\Totp\TotpConfiguration;
use Scheb\TwoFactorBundle\Model\Totp\TotpConfigurationInterface;
use Scheb\TwoFactorBundle\Model\Totp\TwoFactorInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity("email")
 * @Gedmo\Loggable
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface, TwoFactorInterface
{
    const USER_ROLES = [
        'Basic User' => 'ROLE_USER',
        'Directory Manager' => 'ROLE_DIRECTORY_MANAGER',
        'Communications Manager' => 'ROLE_COMMUNICATIONS_MANAGER',
        'Donation Manager' => 'ROLE_DONATION_MANAGER',
        'Event Manager' => 'ROLE_EVENT_MANAGER',
        'Email Manager' => 'ROLE_EMAIL_MANAGER',
        'Site Administrator' => 'ROLE_ADMIN'
    ];

    use TimestampableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Gedmo\Versioned
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     * @Gedmo\Versioned
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @var string|null The plaintext password
     */
    private $plainPassword;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $totpSecret;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Gedmo\Versioned
     */
    private $name;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastLogin;

    /**
     * @ORM\OneToMany(targetEntity=CommunicationLog::class, mappedBy="user")
     */
    private $communicationLogs;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Versioned
     */
    private $timezone;

    public function __construct()
    {
        $this->communicationLogs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        $this->plainPassword = null;
    }

    public function setPlainPassword(string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;
        return $this;
    }

    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    public function getTotpSecret(): string
    {
        return $this->totpSecret;
    }

    public function setTotpSecret(?string $totpSecret): self
    {
        $this->totpSecret = $totpSecret;
        return $this;
    }

    public function getLastLogin(): ?\DateTime {
        return $this->lastLogin;
    }

    public function setLastLogin(?\DateTime $lastLogin): self {
        $this->lastLogin = $lastLogin;

        return $this;
    }

    public function getName(): ?string {
        return $this->name;
    }

    public function setName(?string $name): self {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection|CommunicationLog[]
     */
    public function getCommunicationLogs(): Collection
    {
        return $this->communicationLogs;
    }

    public function addCommunicationLog(CommunicationLog $communicationLog): self
    {
        if (!$this->communicationLogs->contains($communicationLog)) {
            $this->communicationLogs[] = $communicationLog;
            $communicationLog->setUser($this);
        }

        return $this;
    }

    public function removeCommunicationLog(CommunicationLog $communicationLog): self
    {
        if ($this->communicationLogs->removeElement($communicationLog)) {
            // set the owning side to null (unless already changed)
            if ($communicationLog->getUser() === $this) {
                $communicationLog->setUser(null);
            }
        }

        return $this;
    }

    public function getTimezone(): ?string
    {
        return $this->timezone;
    }

    public function setTimezone(?string $timezone): self
    {
        $this->timezone = $timezone;

        return $this;
    }

    /**
     * Model Methods
     */

    public function __toString(): string
    {
        return sprintf('%s (%s)', $this->name, $this->email);
    }

    public function isTotpAuthenticationEnabled(): bool
    {
        return $this->totpSecret ? true : false;
    }

    public function getTotpAuthenticationUsername(): string
    {
        return $this->email;
    }

    public function getTotpAuthenticationConfiguration(): TotpConfigurationInterface
    {
        return new TotpConfiguration($this->totpSecret, TotpConfiguration::ALGORITHM_SHA1, 30, 6);
    }
}
