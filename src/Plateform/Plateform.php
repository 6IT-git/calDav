<?php

namespace App\Plateform;

use InvalidArgumentException;
use App\Plateform\Plateforms\Baikal;
use App\Plateform\Plateforms\Google;
use App\Plateform\Plateforms\Zimbra;
use App\Plateform\PlateformUserInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

abstract class Plateform
{

    protected string $srvUrl;


    /**
     * Undocumented function
     *
     * @param Request $request
     * @return PlateformUserInterface
     */
    abstract public function kokokoo(Request $request): PlateformUserInterface;

    /**
     * Undocumented function
     *
     * @param string $credentials
     * @return array
     */
    abstract public function calendars(string $credentials): array;

    /**
     * Undocumented function
     *
     * @param string $credentials
     * @param string $idCal
     * @return array
     */
    abstract public function events(string $credentials, string $idCal): array;

    /**
     * Undocumented function
     *
     * @param string $credentials
     * @param CalDAVEvent $event
     * @return CalDAVEvent
     */
    abstract public function createEvent(string $credentials, CalDAVEvent $event):CalDAVEvent;

    /**
     * Undocumented function
     *
     * @param string $credentials
     * @param string $name
     * @param string $description
     * @param string $displayName
     * @return void
     */
    abstract public function createCalendar(string $credentials, string $name, string $description, string $displayName = '');

    private static array $_plateformMap = [
        'baikal' => Baikal::class,
        'google' => Google::class,
        'zimbra' => Zimbra::class,
    ];

    /**
     * @param string $type
     * @return self
     */
    public static function create(string $type, ParameterBagInterface $params): self
    {
        if (!array_key_exists($type, self::$_plateformMap)) {
            throw new InvalidArgumentException("Invalid plateform type: $type");
        }

        return new Zimbra($params);

        $className = self::$_plateformMap[$type];
        return new $className($params);
    }

    /**
     * @param string $type
     * @return self
     */
    public function getInstance(string $type): self
    {
        if (!array_key_exists($type, self::$_plateformMap)) {
            throw new InvalidArgumentException("Invalid plateform type: $type");
        }

        $className = self::$_plateformMap[$type];
        return new $className();
    }
}
