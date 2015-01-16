<?php
/**********************************************************\
|                                                          |
| The implementation of PHPRPC Protocol 3.0                |
|                                                          |
| phprpc_date.php                                          |
|                                                          |
| Release 3.0.1                                            |
| Copyright by Team-PHPRPC                                 |
|                                                          |
| WebSite:  http://www.phprpc.org/                         |
|           http://www.phprpc.net/                         |
|           http://www.phprpc.com/                         |
|           http://sourceforge.net/projects/php-rpc/       |
|                                                          |
| Authors:  Ma Bingyao <andot@ujn.edu.cn>                  |
|                                                          |
| This file may be distributed and/or modified under the   |
| terms of the GNU General Public License (GPL) version    |
| 2.0 as published by the Free Software Foundation and     |
| appearing in the included file LICENSE.                  |
|                                                          |
\**********************************************************/

/* PHPRPC_Date Class for PHP.
 *
 * Copyright: Ma Bingyao <andot@ujn.edu.cn>
 * Version: 1.2
 * LastModified: Apr 12, 2010
 * This library is free.  You can redistribute it and/or modify it under GPL.
 */

class PHPRPC_Date {

// public fields

    var $year = 1;
    var $month = 1;
    var $day = 1;
    var $hour = 0;
    var $minute = 0;
    var $second = 0;
    var $millisecond = 0;

// constructor

    function PHPRPC_Date() {
        $num = func_num_args();
        $time = false;
        if ($num == 0) {
            $time = getdate();
        }
        if ($num == 1) {
            $arg = func_get_arg(0);
            if (is_int($arg)) {
                $time = getdate($arg);
            }
            elseif (is_string($arg)) {
                $time = getdate(strtotime($arg));
            }
        }
        if (is_array($time)) {
            $this->year = $time['year'];
            $this->month = $time['mon'];
            $this->day = $time['mday'];
            $this->hour = $time['hours'];
            $this->minute = $time['minutes'];
            $this->second = $time['seconds'];
        }
    }

// public instance methods

    function addMilliseconds($milliseconds) {
        if (!is_int($milliseconds)) return false;
        if ($milliseconds == 0) return true;
        $millisecond = $this->millisecond + $milliseconds;
        $milliseconds = $millisecond % 1000;
        if ($milliseconds < 0) {
            $milliseconds += 1000;
        }
        $seconds = (int)(($millisecond - $milliseconds) / 1000);
        $millisecond = (int)$milliseconds;
        if ($this->addSeconds($seconds)) {
            $this->millisecond = (int)$milliseconds;
            return true;
        }
        else {
            return false;
        }
    }

    function addSeconds($seconds) {
        if (!is_int($seconds)) return false;
        if ($seconds == 0) return true;
        $second = $this->second + $seconds;
        $seconds = $second % 60;
        if ($seconds < 0) {
            $seconds += 60;
        }
        $minutes = (int)(($second - $seconds) / 60);
        if ($this->addMinutes($minutes)) {
            $this->second = (int)$seconds;
            return true;
        }
        else {
            return false;
        }
    }

    function addMinutes($minutes) {
        if (!is_int($minutes)) return false;
        if ($minutes == 0) return true;
        $minute = $this->minute + $minutes;
        $minutes = $minute % 60;
        if ($minutes < 0) {
            $minutes += 60;
        }
        $hours = (int)(($minute - $minutes) / 60);
        if ($this->addHours($hours)) {
            $this->minute = (int)$minutes;
            return true;
        }
        else {
            return false;
        }
    }

    function addHours($hours) {
        if (!is_int($hours)) return false;
        if ($hours == 0) return true;
        $hour = $this->hour + $hours;
        $hours = $hour % 24;
        if ($hours < 0) {
            $hours += 24;
        }
        $days = (int)(($hour - $hours) / 24);
        if ($this->addDays($days)) {
            $this->hour = (int)$hours;
            return true;
        }
        else {
            return false;
        }
    }

    function addDays($days) {
        if (!is_int($days)) return false;
        $year = $this->year;
        if ($days == 0) return true;
        if ($days >= 146097 || $days <= -146097) {
            $remainder = $days % 146097;
            if ($remainder < 0) {
                $remainder += 146097;
            }
            $years = 400 * (int)(($days - $remainder) / 146097);
            $year += $years;
            if ($year < 1 || $year > 9999) return false;
            $days = $remainder;
        }
        if ($days >= 36524 || $days <= -36524) {
            $remainder = $days % 36524;
            if ($remainder < 0) {
                $remainder += 36524;
            }
            $years = 100 * (int)(($days - $remainder) / 36524);
            $year += $years;
            if ($year < 1 || $year > 9999) return false;
            $days = $remainder;
        }
        if ($days >= 1461 || $days <= -1461) {
            $remainder = $days % 1461;
            if ($remainder < 0) {
                $remainder += 1461;
            }
            $years = 4 * (int)(($days - $remainder) / 1461);
            $year += $years;
            if ($year < 1 || $year > 9999) return false;
            $days = $remainder;
        }
        $month = $this->month;
        while ($days >= 365) {
            if ($year >= 9999) return false;
            if ($month <= 2) {
                if ((($year % 4) == 0) ? (($year % 100) == 0) ? (($year % 400) == 0) : true : false) {
                    $days -= 366;
                }
                else {
                    $days -= 365;
                }
                $year++;
            }
            else {
                $year++;
                if ((($year % 4) == 0) ? (($year % 100) == 0) ? (($year % 400) == 0) : true : false) {
                    $days -= 366;
                }
                else {
                    $days -= 365;
                }
            }
        }
        while ($days < 0) {
            if ($year <= 1) return false;
            if ($month <= 2) {
                $year--;
                if ((($year % 4) == 0) ? (($year % 100) == 0) ? (($year % 400) == 0) : true : false) {
                    $days += 366;
                }
                else {
                    $days += 365;
                }
            }
            else {
                if ((($year % 4) == 0) ? (($year % 100) == 0) ? (($year % 400) == 0) : true : false) {
                    $days += 366;
                }
                else {
                    $days += 365;
                }
                $year--;
            }
        }
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $day = $this->day;
        while ($day + $days > $daysInMonth) {
            $days -= $daysInMonth - $day + 1;
            $month++;
            if ($month > 12) {
                if ($year >= 9999) return false;
                $year++;
                $month = 1;
            }
            $day = 1;
            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        }
        $day += $days;
        $this->year = $year;
        $this->month = $month;
        $this->day = $day;
        return true;
    }

    function addMonths($months) {
        if (!is_int($months)) return false;
        if ($months == 0) return true;
        $month = $this->month + $months;
        $months = ($month - 1) % 12 + 1;
        if ($months < 1) {
            $months += 12;
        }
        $years = (int)(($month - $months) / 12);
        if ($this->addYears($years)) {
            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $months, $this->year);
            if ($this->day > $daysInMonth) {
                $months++;
                $this->day -= $daysInMonth;
            }
            $this->month = (int)$months;
            return true;
        }
        else {
            return false;
        }
    }

    function addYears($years) {
        if (!is_int($years)) return false;
        if ($years == 0) return true;
        $year = $this->year + $years;
        if ($year < 1 || $year > 9999) return false;
        $this->year = $year;
        return true;
    }

    function after($when) {
        if (!is_a($when, 'PHPRPC_Date')) {
            $when = PHPRPC_Date::parse($when);
        }
        if ($this->year < $when->year) return false;
        if ($this->year > $when->year) return true;
        if ($this->month < $when->month) return false;
        if ($this->month > $when->month) return true;
        if ($this->day < $when->day) return false;
        if ($this->day > $when->day) return true;
        if ($this->hour < $when->hour) return false;
        if ($this->hour > $when->hour) return true;
        if ($this->minute < $when->minute) return false;
        if ($this->minute > $when->minute) return true;
        if ($this->second < $when->second) return false;
        if ($this->second > $when->second) return true;
        if ($this->millisecond < $when->millisecond) return false;
        if ($this->millisecond > $when->millisecond) return true;
        return false;
    }

    function before($when) {
        if (!is_a($when, 'PHPRPC_Date')) {
            $when = new PHPRPC_Date($when);
        }
        if ($this->year < $when->year) return true;
        if ($this->year > $when->year) return false;
        if ($this->month < $when->month) return true;
        if ($this->month > $when->month) return false;
        if ($this->day < $when->day) return true;
        if ($this->day > $when->day) return false;
        if ($this->hour < $when->hour) return true;
        if ($this->hour > $when->hour) return false;
        if ($this->minute < $when->minute) return true;
        if ($this->minute > $when->minute) return false;
        if ($this->second < $when->second) return true;
        if ($this->second > $when->second) return false;
        if ($this->millisecond < $when->millisecond) return true;
        if ($this->millisecond > $when->millisecond) return false;
        return false;
    }

    function equals($when) {
        if (!is_a($when, 'PHPRPC_Date')) {
            $when = new PHPRPC_Date($when);
        }
        return (($this->year == $when->year) &&
            ($this->month == $when->month) &&
            ($this->day == $when->day) &&
            ($this->hour == $when->hour) &&
            ($this->minute == $when->minute) &&
            ($this->second == $when->second) &&
            ($this->millisecond == $when->millisecond));
    }

    function set() {
        $num = func_num_args();
        $args = func_get_args();
        if ($num >= 3) {
            if (!PHPRPC_Date::isValidDate($args[0], $args[1], $args[2])) {
                return false;
            }
            $this->year  = (int)$args[0];
            $this->month = (int)$args[1];
            $this->day   = (int)$args[2];
            if ($num == 3) {
                return true;
            }
        }
        if ($num >= 6) {
            if (!PHPRPC_Date::isValidTime($args[3], $args[4], $args[5])) {
                return false;
            }
            $this->hour   = (int)$args[3];
            $this->minute = (int)$args[4];
            $this->second = (int)$args[5];
            if ($num == 6) {
                return true;
            }
        }
        if (($num == 7) && ($args[6] >= 0 && $args[6] <= 999)) {
            $this->millisecond = (int)$args[6];
            return true;
        }
        return false;
    }

    function time() {
        return mktime($this->hour, $this->minute, $this->second, $this->month, $this->day, $this->year);
    }

    function toString() {
        return sprintf('%04d-%02d-%02d %02d:%02d:%02d.%03d',
            $this->year, $this->month, $this->day,
            $this->hour, $this->minute, $this->second,
            $this->millisecond);
    }

// magic method for PHP 5

    function __toString() {
        return $this->toString();
    }

// public instance & static methods

    function dayOfWeek() {
        $num = func_num_args();
        if ($num == 3) {
            $args = func_get_args();
            $y = $args[0];
            $m = $args[1];
            $d = $args[2];
        }
        else {
            $y = $this->year;
            $m = $this->month;
            $d = $this->day;
        }
        $d += $m < 3 ? $y-- : $y - 2;
        return ((int)(23 * $m / 9) + $d + 4 + (int)($y / 4) - (int)($y / 100) + (int)($y / 400)) % 7;
    }

    function dayOfYear() {
        static $daysToMonth365 = array(0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334, 365);
        static $daysToMonth366 = array(0, 31, 60, 91, 121, 152, 182, 213, 244, 274, 305, 335, 366);
        $num = func_num_args();
        if ($num == 3) {
            $args = func_get_args();
            $y = $args[0];
            $m = $args[1];
            $d = $args[2];
        }
        else {
            $y = $this->year;
            $m = $this->month;
            $d = $this->day;
        }
        $days = PHPRPC_Date::isLeapYear($y) ? $daysToMonth365 : $daysToMonth366;
        return $days[$m - 1] + $d;
    }

// public static methods

    function now() {
        $date = new PHPRPC_Date();
        return $date;
    }

    function today() {
        $date = PHPRPC_Date::now();
        $date->hour = 0;
        $date->minute = 0;
        $date->second = 0;
        return $date;
    }

    function parse($dt) {
        if (is_a($dt, 'PHPRPC_Date')) {
            return $dt;
        }
        if (is_int($dt)) {
            return new PHPRPC_Date($dt);
        }
        $shortFormat = '(\d|\d{2}|\d{3}|\d{4})-([1-9]|0[1-9]|1[012])-([1-9]|0[1-9]|[12]\d|3[01])';
        if (preg_match("/^$shortFormat$/", $dt, $match)) {
            $year   = intval($match[1]);
            $month  = intval($match[2]);
            $day    = intval($match[3]);
            if (PHPRPC_Date::isValidDate($year, $month, $day)) {
                $date = new PHPRPC_Date(false);
                $date->year  = $year;
                $date->month = $month;
                $date->day   = $day;
                return $date;
            }
            else {
                return false;
            }
        }
        $longFormat = $shortFormat . ' (\d|0\d|1\d|2[0-3]):(\d|[0-5]\d):(\d|[0-5]\d)';
        if (preg_match("/^$longFormat$/", $dt, $match)) {
            $year   = intval($match[1]);
            $month  = intval($match[2]);
            $day    = intval($match[3]);
            if (PHPRPC_Date::isValidDate($year, $month, $day)) {
                $date = new PHPRPC_Date(false);
                $date->year  = $year;
                $date->month = $month;
                $date->day   = $day;
                $date->hour   = intval($match[4]);
                $date->minute = intval($match[5]);
                $date->second = intval($match[6]);
                return $date;
            }
            else {
                return false;
            }
        }
        $fullFormat = $longFormat . '\.(\d|\d{2}|\d{3})';
        if (preg_match("/^$fullFormat$/", $dt, $match)) {
            $year   = intval($match[1]);
            $month  = intval($match[2]);
            $day    = intval($match[3]);
            if (PHPRPC_Date::isValidDate($year, $month, $day)) {
                $date = new PHPRPC_Date(false);
                $date->year  = $year;
                $date->month = $month;
                $date->day   = $day;
                $date->hour        = intval($match[4]);
                $date->minute      = intval($match[5]);
                $date->second      = intval($match[6]);
                $date->millisecond = intval($match[7]);
                return $date;
            }
            else {
                return false;
            }
        }
        return false;
    }

    function isLeapYear($year) {
        return (($year % 4) == 0) ? (($year % 100) == 0) ? (($year % 400) == 0) : true : false;
    }

    function daysInMonth($year, $month) {
        if (($month < 1) || ($month > 12)) {
            return false;
        }
        return cal_days_in_month(CAL_GREGORIAN, $month, $year);
    }

    function isValidDate($year, $month, $day) {
        if (($year >= 1) && ($year <= 9999)) {
            return checkdate($month, $day, $year);
        }
        return false;
    }

    function isValidTime($hour, $minute, $second) {
        return !(($hour < 0) || ($hour > 23) ||
            ($minute < 0) || ($minute > 59) ||
            ($second < 0) || ($second > 59));
    }
}
?>