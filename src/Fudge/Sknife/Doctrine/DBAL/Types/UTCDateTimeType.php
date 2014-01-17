<?php

namespace Fudge\Sknife\Doctrine\DBAL\Types;

use Doctrine\DBAL\Types\DateTimeType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;

/**
 * Handles datetime timezone conversion to UTC in database
 * @since 09/09/2013
 * @author Yohann Marillet <yohann.marillet@gmail.com>
 * @see http://docs.doctrine-project.org/en/2.0.x/cookbook/working-with-datetime.html
 */
class UTCDateTimeType extends DateTimeType
{
    private static $utc = null;
    private static $phpTimezone = null;

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }

        if (is_null(self::$utc)) {
            self::$utc = new \DateTimeZone('UTC');
        }

        $value->setTimeZone(self::$utc);

        return $value->format($platform->getDateTimeFormatString());
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }

        if (is_null(self::$utc)) {
            self::$utc = new \DateTimeZone('UTC');
        }

        $val = \DateTime::createFromFormat($platform->getDateTimeFormatString(), $value, self::$utc);

        if (!$val) {
            throw ConversionException::conversionFailed($value, $this->getName());
        }

        if ( (is_null(self::$phpTimezone))) {
            self::$phpTimezone = new \DateTimeZone(date_default_timezone_get());
        }

        $val->setTimezone(clone self::$phpTimezone);

        return $val;
    }

    public static function setPhpDefaultTimezone($timezone)
    {
        self::$phpTimezone = new \DateTimeZone($timezone);
    }
}
