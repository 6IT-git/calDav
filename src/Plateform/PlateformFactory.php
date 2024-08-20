<?php

namespace App\Plateform;

use App\Entity\EventDto;
use InvalidArgumentException;
use App\plateform\plateforms\Baikal;
use App\plateform\plateforms\Google;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class PlateformFactory
{

    private $parameter;

    public function __construct(ParameterBagInterface $parameter){
        $this->parameter = $parameter;
    }

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
