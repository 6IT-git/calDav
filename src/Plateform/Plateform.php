<?php

namespace App\Plateform;

use InvalidArgumentException;
use App\Plateform\Plateforms\Baikal;
use App\Plateform\Plateforms\Google;
use App\Plateform\Plateforms\Zimbra;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

abstract class Plateform implements PlateformInterface
{

    protected string $srvUrl;

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
