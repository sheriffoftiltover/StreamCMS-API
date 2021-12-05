<?php
namespace Destiny\Common\Utils;

use DateInterval;
use DateTime;
use DateTimeZone;
use Exception;

/**
 * This class is bad
 */
abstract class Date {
    
    const STRING_FORMAT_YEAR = 'g:ia, D jS F Y e';
    const STRING_FORMAT = 'M jS, Y g:iA T';
    const FORMAT = DATE_ISO8601;

    public static function now(): DateTime {
        return new DateTime();
    }

    public static function getDateTime($time = 'NOW'): DateTime {
        try {
            if (!is_numeric($time)) {
                $date = new DateTime($time);
            } else {
                $date = new DateTime();
                $date->setTimestamp($time);
            }
        } catch (Exception $e) {
            $date = new DateTime();
        }
        if ($date != null) {
            $date->setTimezone(new DateTimeZone(ini_get('date.timezone')));
        }
        return $date;
    }

    public static function getDateTimePlusSeconds($time = 'NOW', int $seconds = 0): DateTime {
        $date = Date::getDateTime($time);
        try { $date->add(new DateInterval("PT". $seconds ."S")); } catch (Exception $e) {/* IGNORED */}
        return $date;
    }

    public static function getSqlDateTimePlusSeconds($time = 'NOW', int $seconds = 0): string {
        return self::getDateTimePlusSeconds($time, $seconds)->format('Y-m-d H:i:s');
    }

    public static function getSqlDateTime($time = 'NOW'): string {
        return self::getDateTime($time)->format('Y-m-d H:i:s');
    }

    /**
     * Interval formatting, will use the two biggest interval parts.
     * On small intervals, you get minutes and seconds.
     * On big intervals, you get months and days.
     * Only the two biggest parts are used.
     */
    public static function getRemainingTime(DateTime $start, DateTime $end = null): string {
        if (! ($start instanceof DateTime)) {
            $start = self::getDateTime ( $start );
        }
        if ($end === null) {
            $end = self::getDateTime ();
        }
        if (! ($end instanceof DateTime)) {
            $end = self::getDateTime ( $end );
        }
        $interval = $end->diff ( $start );
        $format = [];
        if ($interval->y !== 0) {
            $format [] = "%y " . self::getIntervalPlural ( $interval->y, "year" );
        }
        if ($interval->m !== 0) {
            $format [] = "%m " . self::getIntervalPlural ( $interval->m, "month" );
        }
        if ($interval->d !== 0) {
            $format [] = "%d " . self::getIntervalPlural ( $interval->d, "day" );
        }
        if ($interval->h !== 0) {
            $format [] = "%h " . self::getIntervalPlural ( $interval->h, "hour" );
        }
        if ($interval->i !== 0) {
            $format [] = "%i " . self::getIntervalPlural ( $interval->i, "minute" );
        }
        if ($interval->s !== 0) {
            $format [] = "%s " . self::getIntervalPlural ( $interval->s, "second" );
        }
        // We use the two biggest parts
        if (count ( $format ) > 1) {
            $format = array_shift ( $format ) . " and " . array_shift ( $format );
        } else {
            $format = array_pop ( $format );
        }
        // Prepend 'since ' or whatever you like
        return (($start < $end) ? '-' : '') . $interval->format ( $format );
    }
    
    private static function getIntervalPlural(int $nb, string $str): string {
        return $nb > 1 ? $str . 's' : $str;
    }

    public static function getElapsedTime(DateTime $date, DateTime $compareTo = null): string {
        if (is_null($compareTo)) {
            $compareTo = self::getDateTime();
        }
        $diff = $compareTo->format('U') - $date->format('U');
        $dayDiff = floor($diff / 86400);
        if (is_nan($dayDiff) || $dayDiff < 0) {
            return '' . $diff;
        }
        if ($dayDiff == 0) {
            if ($diff < 60) {
                return 'Just now';
            } elseif ($diff < 120) {
                return '1 minute ago';
            } elseif ($diff < 3600) {
                return floor($diff / 60) . ' minutes ago';
            } elseif ($diff < 7200) {
                return '1 hour ago';
            } elseif ($diff < 86400) {
                return floor($diff / 3600) . ' hours ago';
            }
        } elseif ($dayDiff == 1) {
            return 'Yesterday';
        } elseif ($dayDiff < 7) {
            return $dayDiff . ' days ago';
        } elseif ($dayDiff == 7) {
            return '1 week ago';
        } elseif ($dayDiff < (7 * 6)) {
            return ceil($dayDiff / 7) . ' weeks ago';
        } elseif ($dayDiff < 365) {
            return ceil($dayDiff / (365 / 12)) . ' months ago';
        }
        $years = round($dayDiff / 365);
        return $years . ' year' . ($years != 1 ? 's' : '') . ' ago';
    }

}