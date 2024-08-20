<?php

namespace App\Plateform;

use App\Entity\EventDto;
use InvalidArgumentException;
use App\Plateform\Plateforms\Baikal;
use App\Plateform\Plateforms\Google;
use App\Security\User;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;

abstract class Plateform
{
    protected string $srvUrl;

    abstract public function login(Request $request): User;

    /**
     * get all calendars
     *
     * @return array
     */
    abstract public function getCalendars(): array;

    /**
     * get all events
     *
     * @param string $idCal
     * @param EventDto $event
     * @return array
     */
    abstract public function getEvents(string $idCal, EventDto $event): array;

    /**
     * add new event
     *
     * @param string $calID
     * @param EventDto $event
     * @return string
     */
    abstract public function addEvent(string $calID, EventDto $event): string;


    // Table de mappage des types aux classes
    private static array $plateformMap = [
        'baikal' => Baikal::class,
        'goolge' => Google::class,
        // 'zimbra' => Zimbra::class,
    ];

    public static function create(string $type): self
    {
        if (!array_key_exists($type, self::$plateformMap)) {
            throw new InvalidArgumentException("Invalid product type: $type");
        }

        $className = self::$plateformMap[$type];
        return new $className();
    }
}
