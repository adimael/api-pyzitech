<?php

namespace src\Utils;

final class RelogioTimeZone
{
    private static ?\DateTimeZone $timeZone = null;

    private function __construct()
    {
    }

    public static function obterTimeZone(): \DateTimeZone
    {
        if (self::$timeZone === null)
        {
            $timeZoneString = getenv('APP_TIMEZONE');
            self::$timeZone = new \DateTimeZone($timeZoneString);
        }

        return self::$timeZone;
    }

    public static function agora(): \DateTimeImmutable
    {
        return new \DateTimeImmutable('now', self::obterTimeZone());
    }
}