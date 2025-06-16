<?php

declare(strict_types=1);

/*
 * This file is part of the AstroBook project.
 * (c) David Pelletier-Ulrich <d@mztrix.me>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dogstronauts\AstroBook\Bookings\Model;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use Doctrine\ORM\Mapping as ORM;
use Dogstronauts\AstroBook\Events\Model\Event;
use Dogstronauts\AstroBook\Security\Model\User;
use Symfony\Bridge\Doctrine\IdGenerator\UlidGenerator;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\Serializer\Attribute as Serializer;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'booking')]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(security: "is_granted('ROLE_PLATFORM')"),
        new Patch(security: "is_granted('ROLE_PLATFORM')"),
        new Delete(security: "is_granted('ROLE_PLATFORM')"),
    ],
    normalizationContext: ['groups' => ['booking:read']],
    denormalizationContext: ['groups' => ['booking:write']],
)]
class Booking
{
    #[ORM\Id]
    #[ORM\Column(type: UlidType::NAME)]
    #[ORM\CustomIdGenerator(class: UlidGenerator::class)]
    #[ORM\GeneratedValue('CUSTOM')]
    #[Serializer\Groups(['booking:read'])]
    public Ulid $id;

    #[ORM\Column(length: 9, enumType: BookingStatus::class)]
    #[Assert\NotBlank]
    #[Assert\Choice(callback: [BookingStatus::class, 'cases'])]
    #[Serializer\Groups(['booking:read', 'booking:write'])]
    public BookingStatus $status = BookingStatus::PENDING;

    #[ORM\ManyToOne(inversedBy: 'bookings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Event $event = null;

    #[ORM\ManyToOne(inversedBy: 'bookings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $bookedBy = null;

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): static
    {
        $this->event = $event;

        return $this;
    }

    public function getBookedBy(): ?User
    {
        return $this->bookedBy;
    }

    public function setBookedBy(?User $bookedBy): static
    {
        $this->bookedBy = $bookedBy;

        return $this;
    }
}
