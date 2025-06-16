<?php

declare(strict_types=1);

/*
 * This file is part of the AstroBook project.
 * (c) David Pelletier-Ulrich <d@mztrix.me>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dogstronauts\AstroBook\Security\Model;

use ApiPlatform\Metadata as ApiMetadata;
use ApiPlatform\OpenApi\Model\Operation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Dogstronauts\AstroBook\Bookings\Model\Booking;
use Dogstronauts\AstroBook\Security\ApiPlatform\State\UserProcessor;
use Symfony\Bridge\Doctrine\IdGenerator\UlidGenerator;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute as Serializer;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\PasswordStrength;

/**
 * Represents a user account within the application.
 *
 * Used to create, authenticate, and manage users who access the platform,
 * with permissions defined through configurable roles.
 */
#[ORM\Entity]
#[ORM\Table(name: '`user`')]
#[DoctrineAssert\UniqueEntity(fields: ['identifier'])]
#[ApiMetadata\ApiResource(
    normalizationContext: ['groups' => ['user:read']],
    denormalizationContext: ['groups' => ['user:write']],
    openapi: new Operation(tags: ['Security']),
    processor: UserProcessor::class,
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    public function __construct()
    {
        $this->bookings = new ArrayCollection();
    }

    #[ORM\Id]
    #[ORM\Column(type: UlidType::NAME)]
    #[ORM\CustomIdGenerator(class: UlidGenerator::class)]
    #[ORM\GeneratedValue('CUSTOM')]
    #[Serializer\Groups(['user:read'])]
    public Ulid $id;

    #[ORM\Column(type: Types::STRING, length: 128, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 128)]
    #[Serializer\Groups(['user:read', 'user:write'])]
    public string $identifier;

    #[ORM\Column(type: Types::STRING, length: 128)]
    public string $password;

    #[Assert\Length(min: 8, max: 128)]
    #[PasswordStrength(
        minScore: PasswordStrength::STRENGTH_MEDIUM,
    )]
    #[Serializer\Groups(['user:write'])]
    public ?string $plainPassword = null;

    /** @var list<string> */
    #[ORM\Column(type: Types::JSON)]
    #[Assert\NotNull]
    #[Assert\All([
        new Assert\NotBlank(),
        new Assert\Regex(pattern: '/^ROLE_[A-Z_]+$/'),
    ])]
    #[Serializer\Groups(['user:read', 'user:write'])]
    public array $roles = [];

    /**
     * @var Collection<int, Booking>
     */
    #[ORM\OneToMany(targetEntity: Booking::class, mappedBy: 'bookedBy')]
    private Collection $bookings;


    public function getPassword(): string
    {
        return $this->password;
    }

    /** @return list<string> A list of roles (e.g., 'ROLE_USER', 'ROLE_ADMIN'). */
    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getUserIdentifier(): string
    {
        return $this->identifier;
    }

    #[\Deprecated(
        message: 'The "eraseCredentials()" method is deprecated since Symfony 7.3. It will be removed in Symfony 8.0.',
        since: '7.3'
    )]
    public function eraseCredentials(): void {}

    /**
     * @return Collection<int, Booking>
     */
    public function getBookings(): Collection
    {
        return $this->bookings;
    }

    public function addBooking(Booking $booking): static
    {
        if (!$this->bookings->contains($booking)) {
            $this->bookings->add($booking);
            $booking->setBookedBy($this);
        }

        return $this;
    }

    public function removeBooking(Booking $booking): static
    {
        if ($this->bookings->removeElement($booking)) {
            // set the owning side to null (unless already changed)
            if ($booking->getBookedBy() === $this) {
                $booking->setBookedBy(null);
            }
        }

        return $this;
    }
}
