<?php

declare(strict_types=1);

namespace App;

class Utils
{


    private const MYSQL_DATETIME_FORMAT = 'Y-m-d H:i:s';
    public static function parseDateTime(string $value, string $format): ?\DateTimeImmutable
    {
        if ($value != null) {
            $result = \DateTimeImmutable::createFromFormat($format, $value);
            if (!$result) {
                throw new \InvalidArgumentException("Invalid datetime value '$value'");
            }
            return $result;
        }else{
            return null;
        }

    }
    public static function convertDataTimeToFormat(\DateTimeImmutable $date): string
    {

        $datetime = $date->getTimestamp();
        return date('dd.mm.YYYY', $datetime);
    }
    public static function convertDataTimeToString(?\DateTimeImmutable $date): ?string //дата опциональна поэтому ?
    {
        if ($date === null) //если пусто то возвращает null
        {
            return null;
        }
        return $date->format(self::MYSQL_DATETIME_FORMAT);
    }
}