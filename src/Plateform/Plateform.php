<?php

namespace App\Plateform;

use InvalidArgumentException;
use App\Plateform\Plateforms\Baikal;
use App\Plateform\Plateforms\Google;
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
     * @param PlateformUserInterface $user
     * @return array
     */
    abstract public function calendars(PlateformUserInterface $user): array;

    /**
     * Undocumented function
     *
     * @param PlateformUserInterface $user
     * @return array
     */
    abstract public function events(PlateformUserInterface $user): array;

    /**
     * Undocumented function
     *
     * @param PlateformUserInterface $user
     * @param CalDAVEvent $event
     * @return CalDAVEvent
     */
    abstract public function createEvent(PlateformUserInterface $user, CalDAVEvent $event):CalDAVEvent;


    private static array $_plateformMap = [
        'baikal' => Baikal::class,
        'google' => Google::class,
        // 'zimbra' => Zimbra::class,
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
