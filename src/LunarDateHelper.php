<?php

namespace Jankx\LunarCanlendar;

use Jetfuel\SolarLunar\SolarLunar;
use Jetfuel\SolarLunar\Solar;

/**
 * Lunar Date Helper
 *
 * Helper class to calculate lunar date from gregorian date with cache
 * Uses jetfueltw/solarlunar-php library
 *
 * @package Jankx\LunarCanlendar
 * @link https://github.com/jetfueltw/solarlunar-php
 */
class LunarDateHelper
{
    /**
     * Cache key prefix
     */
    const CACHE_PREFIX = 'jankx_lunar_date_';

    /**
     * Cache expiration (1 day)
     */
    const CACHE_EXPIRATION = DAY_IN_SECONDS;

    /**
     * Can (Heavenly Stems)
     *
     * @var array
     */
    protected static $can = ['Giap', 'At', 'Binh', 'Dinh', 'Mau', 'Ky', 'Canh', 'Tan', 'Nham', 'Quy'];

    /**
     * Chi (Earthly Branches)
     *
     * @var array
     */
    protected static $chi = ['Ty', 'Suu', 'Dan', 'Mao', 'Thin', 'Ti', 'Ngo', 'Mui', 'Than', 'Dau', 'Tuat', 'Hoi'];

    /**
     * Get Can Chi for year
     *
     * @param int $lunarYear
     * @return string
     */
    protected static function getYearCanChi($lunarYear)
    {
        $canIndex = ($lunarYear + 6) % 10;
        $chiIndex = ($lunarYear + 8) % 12;
        
        return self::$can[$canIndex] . ' ' . self::$chi[$chiIndex];
    }

    /**
     * Get Can Chi for month
     *
     * @param int $lunarMonth
     * @param int $lunarYear
     * @return string
     */
    protected static function getMonthCanChi($lunarMonth, $lunarYear)
    {
        $canIndex = ($lunarYear * 12 + $lunarMonth + 3) % 10;
        $chiIndex = ($lunarMonth + 1) % 12;
        
        return self::$can[$canIndex] . ' ' . self::$chi[$chiIndex];
    }

    /**
     * Get Julian Day Number from Gregorian date
     *
     * @param int $day
     * @param int $month
     * @param int $year
     * @return int
     */
    protected static function getJulianDayNumber($day, $month, $year)
    {
        $a = intval((14 - $month) / 12);
        $y = $year + 4800 - $a;
        $m = $month + 12 * $a - 3;
        
        return $day + intval((153 * $m + 2) / 5) + 365 * $y + intval($y / 4) - intval($y / 100) + intval($y / 400) - 32045;
    }

    /**
     * Get Can Chi for day
     *
     * @param int $day
     * @param int $month
     * @param int $year
     * @return string
     */
    protected static function getDayCanChi($day, $month, $year)
    {
        $jd = self::getJulianDayNumber($day, $month, $year);
        
        $canIndex = ($jd + 9) % 10;
        $chiIndex = ($jd + 1) % 12;
        
        return self::$can[$canIndex] . ' ' . self::$chi[$chiIndex];
    }

    /**
     * Convert Gregorian date to Lunar date with Can Chi
     *
     * @param int $day
     * @param int $month
     * @param int $year
     * @return array
     */
    public static function getLunarDate($day, $month, $year)
    {
        // Check cache first
        $cache_key = self::CACHE_PREFIX . sprintf('%04d_%02d_%02d', $year, $month, $day);
        $cached = wp_cache_get($cache_key, 'lunar_calendar');
        
        if ($cached !== false) {
            return $cached;
        }

        try {
            // Convert to lunar date using jetfueltw/solarlunar-php
            $solar = Solar::create($year, $month, $day);
            $lunar = SolarLunar::solarToLunar($solar);

            // Calculate Can Chi
            $yearCanChi = self::getYearCanChi($lunar->year);
            $monthCanChi = self::getMonthCanChi($lunar->month, $lunar->year);
            $dayCanChi = self::getDayCanChi($day, $month, $year);

            $result = [
                'day' => $lunar->day,
                'month' => $lunar->month,
                'year' => $lunar->year,
                'isLeap' => $lunar->isLeap,
                'yearName' => $yearCanChi,
                'monthName' => $monthCanChi,
                'dayName' => $dayCanChi,
            ];

            // Cache for 1 day
            wp_cache_set($cache_key, $result, 'lunar_calendar', self::CACHE_EXPIRATION);

            return $result;
        } catch (\Exception $e) {
            // Fallback: return empty data
            return [
                'day' => $day,
                'month' => $month,
                'year' => $year,
                'isLeap' => false,
                'yearName' => '-',
                'monthName' => '-',
                'dayName' => '-',
            ];
        }
    }

    /**
     * Get today's lunar date
     *
     * @return array
     */
    public static function getTodayLunarDate()
    {
        return self::getLunarDate(
            (int) date('j'),
            (int) date('n'),
            (int) date('Y')
        );
    }

    /**
     * Clear all lunar date cache
     *
     * @return void
     */
    public static function clearCache()
    {
        wp_cache_flush_group('lunar_calendar');
    }
}

