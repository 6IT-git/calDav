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

    /**
     * Undocumented function
     *
     * @param string $password
     * @return User
     */
    abstract public function kokoko(string $password): User;

    /**
     * @param Request $request
     * @return User
     */
    abstract public function login(Request $request): User;

    /**
     * @param string $password
     * @return array
     */
    abstract public function getCalendars(string $password): array;

 
    /**
     * Undocumented function
     *
     * @param string $idCal
     * @param string $password
     * @return array
     */
    abstract public function getEvents(string $idCal, string $password): array;

    /**
     * @param string $username
     * @param string $password
     * @param string $calID
     * @param EventDto $event
     * @return string
     */
    abstract public function addEvent(string $username, string $password, string $calID, EventDto $event): EventDto;

    private static array $plateformMap = [
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
        if (!array_key_exists($type, self::$plateformMap)) {
            throw new InvalidArgumentException("Invalid plateform type: $type");
        }

        $className = self::$plateformMap[$type];
        return new $className($params);
    }

    /**
     * @param string $type
     * @return self
     */
    public function getInstance(string $type): self
    {
        if (!array_key_exists($type, self::$plateformMap)) {
            throw new InvalidArgumentException("Invalid product type: $type");
        }

        $className = self::$plateformMap[$type];
        return new $className();
    }

}
