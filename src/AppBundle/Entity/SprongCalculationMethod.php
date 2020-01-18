<?php

namespace AppBundle\Entity;

class SprongCalculationMethod
{
    const GEMIDDELDE = 'gemiddelde';
    const EERSTE     = 'eerste';

    /**
     * @return string[]
     */
    public static function allAsString(): array
    {
        return [
            self::GEMIDDELDE,
            self::EERSTE,
        ];
    }
}
