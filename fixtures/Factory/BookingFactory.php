<?php

declare(strict_types=1);

namespace Dogstronauts\AstroBook\Fixtures\Factory;

use Dogstronauts\AstroBook\Bookings\Model\Booking;
use Dogstronauts\AstroBook\Bookings\Model\BookingStatus;
use Dogstronauts\AstroBook\Events\Model\Event;
use Dogstronauts\AstroBook\Security\Model\User;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @internal
 *
 * @extends PersistentProxyObjectFactory<Booking>
 */
class BookingFactory extends PersistentProxyObjectFactory
{
    public static function class(): string
    {
        return Booking::class;
    }

    protected function defaults(): array
    {
        return [
            'status' => self::faker()->randomElement(BookingStatus::cases()),
            'event' => EventFactory::randomOrCreate(),
            'bookedBy' => UserFactory::randomOrCreate(),
        ];
    }
}
