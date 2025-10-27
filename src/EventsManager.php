<?php

namespace Jankx\LunarCanlendar;

use Jetfuel\SolarLunar\SolarLunar;
use Jetfuel\SolarLunar\Solar;

/**
 * Events Manager
 *
 * Manages events from multiple sources with weighted priority:
 * - MySQL database (wp-event-solution plugin): weight 1.0
 * - Lunar events JSON: weight 0.8
 * - Solar events JSON: weight 0.4
 *
 * @package Jankx\LunarCanlendar
 */
class EventsManager
{
    /**
     * Database path
     */
    const DB_PATH = __DIR__ . '/../db';

    /**
     * Weight for MySQL events (highest priority)
     */
    const WEIGHT_MYSQL = 1.0;

    /**
     * Weight for lunar events
     */
    const WEIGHT_LUNAR = 0.8;

    /**
     * Weight for solar events (lowest priority)
     */
    const WEIGHT_SOLAR = 0.4;

    /**
     * Cache events by month
     *
     * @var array
     */
    protected static $cache = [];

    /**
     * Get events for a specific month and year
     *
     * @param int $month Solar month (1-12)
     * @param int $year Solar year
     * @return array
     */
    public static function getEvents($month, $year)
    {
        $cache_key = sprintf('%04d_%02d', $year, $month);

        if (isset(self::$cache[$cache_key])) {
            return self::$cache[$cache_key];
        }

        // Collect events from all sources
        $events = [];

        // 1. Get MySQL events (weight 1.0) - highest priority
        $mysql_events = self::getMysqlEvents($month, $year);
        foreach ($mysql_events as $event) {
            $event['weight'] = self::WEIGHT_MYSQL;
            $event['source'] = 'mysql';
            $events[] = $event;
        }

        // 2. Get Solar events (weight 0.4)
        $solar_events = self::getSolarEvents($month, $year);
        foreach ($solar_events as $event) {
            $event['weight'] = self::WEIGHT_SOLAR;
            $event['source'] = 'solar_json';
            $events[] = $event;
        }

        // 3. Get Lunar events (weight 0.8)
        $lunar_events = self::getLunarEvents($month, $year);
        foreach ($lunar_events as $event) {
            $event['weight'] = self::WEIGHT_LUNAR;
            $event['source'] = 'lunar_json';
            $events[] = $event;
        }

        // Sort by day then by weight (higher weight first)
        usort($events, function ($a, $b) {
            if ($a['day'] === $b['day']) {
                return $b['weight'] <=> $a['weight'];
            }
            return $a['day'] <=> $b['day'];
        });

        // Keep all events (already sorted by weight)
        // If you want to keep only one event per day (highest weight), uncomment:
        // $events = self::removeDuplicates($events);

        self::$cache[$cache_key] = $events;

        return $events;
    }

    /**
     * Get MySQL events from wp-event-solution plugin
     *
     * @param int $month
     * @param int $year
     * @return array
     */
    protected static function getMysqlEvents($month, $year)
    {
        global $wpdb;

        // Calculate start and end date
        $start_date = sprintf('%04d-%02d-01 00:00:00', $year, $month);
        $last_day = date('t', strtotime($start_date));
        $end_date = sprintf('%04d-%02d-%02d 23:59:59', $year, $month, $last_day);

        // Query events from wp-event-solution plugin (post type: etn)
        $sql = $wpdb->prepare("
            SELECT
                p.ID as post_id,
                p.post_title as event_name,
                p.post_excerpt,
                p.post_content,
                p.post_status,
                pm_start.meta_value as start_date
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm_start ON p.ID = pm_start.post_id AND pm_start.meta_key = 'etn_start_date'
            WHERE p.post_type = 'etn'
            AND p.post_status = 'publish'
            AND pm_start.meta_value >= %s
            AND pm_start.meta_value <= %s
            ORDER BY pm_start.meta_value ASC
        ", $start_date, $end_date);

        $results = $wpdb->get_results($sql);

        if (!$results) {
            return [];
        }

        // Map category slug to event type
        $category_type_map = apply_filters('lunar-calendar/category-type-map', [
            'lich-su' => 'historical',
            'quoc-gia' => 'national',
            'quoc-te' => 'international',
            'nghe-nghiep' => 'professional',
            'xa-hoi' => 'social',
            'tuong-niem' => 'memorial',
            'le-hoi' => 'celebration',
            'van-hoa' => 'cultural',
            'ton-giao' => 'religious',
        ]);

        $type_map = apply_filters('lunar-calendar/type-number-map', [
            'default' => 0,
            'national' => 1,
            'historical' => 2,
            'international' => 3,
            'professional' => 4,
            'social' => 5,
            'memorial' => 6,
            'celebration' => 7,
            'cultural' => 8,
            'religious' => 9,
        ]);

        $events = [];

        foreach ($results as $row) {
            $event_start_date_str = get_post_meta($row->post_id, 'etn_start_date', true);
            if (!$event_start_date_str) {
                continue;
            }

            $event_start_date = new \DateTime($event_start_date_str);
            $day = intval($event_start_date->format('j'));

            $event_type = 'default';
            $categories = get_the_terms($row->post_id, 'etn_category');
            if (!empty($categories) && !is_wp_error($categories)) {
                $first_category = reset($categories);
                $category_slug = $first_category->slug;

                if (isset($category_type_map[$category_slug])) {
                    $event_type = $category_type_map[$category_slug];
                }
            }

            $type_number = isset($type_map[$event_type]) ? $type_map[$event_type] : 0;

            // Get event year
            $event_year = null;
            $years_ago = null;
            $stored_year = get_post_meta($row->post_id, '_event_year', true);
            if ($stored_year && is_numeric($stored_year)) {
                $event_year = intval($stored_year);
                $years_ago = date('Y') - $event_year;
            }

            // Create description
            $description = '';
            if (!empty($row->post_excerpt)) {
                $description = $row->post_excerpt;
            } elseif (!empty($row->post_content)) {
                $description = wp_trim_words($row->post_content, 20, '...');
            }

            // Add historical info
            if (!empty($description) && $event_year && $years_ago > 0) {
                $description .= ' (' . $event_year . ') - ' . sprintf(
                    _n('%d year ago', '%d years ago', $years_ago, 'lunar-calendar'),
                    $years_ago
                );
            }

            $location_info = get_post_meta($row->post_id, 'etn_event_location', true) ?: '';

            // Check recurring
            $is_recurring = false;
            $recurrence_pattern = '';

            $recurrence_freq = get_post_meta($row->post_id, '_event_recurrence_freq', true);
            $recurrence_interval = get_post_meta($row->post_id, '_event_recurrence_interval', true);

            if ($recurrence_freq) {
                $is_recurring = true;

                $freq_map = [
                    'daily' => __('daily', 'lunar-calendar'),
                    'weekly' => __('weekly', 'lunar-calendar'),
                    'monthly' => __('monthly', 'lunar-calendar'),
                    'yearly' => __('yearly', 'lunar-calendar'),
                ];

                if (isset($freq_map[$recurrence_freq])) {
                    $interval = $recurrence_interval ?: 1;
                    if ($interval == 1) {
                        $recurrence_pattern = $freq_map[$recurrence_freq];
                    } else {
                        $recurrence_pattern = sprintf(
                            __('every %d %s', 'lunar-calendar'),
                            $interval,
                            $freq_map[$recurrence_freq]
                        );
                    }
                }
            }

            $event_end_date_str = get_post_meta($row->post_id, 'etn_end_date', true);
            $end_date = $event_end_date_str ?: $event_start_date_str;

            $events[] = [
                'day' => $day,
                'title' => $row->event_name ?: __('Event', 'lunar-calendar'),
                'year' => $event_year,
                'yearsAgo' => $years_ago,
                'type' => $type_number,
                'typeName' => ucfirst($event_type),
                'description' => $description,
                'isToday' => $event_start_date->format('Y-m-d') === date('Y-m-d'),
                'isHoliday' => in_array($event_type, ['national', 'international']),
                'event_id' => $row->post_id,
                'post_id' => $row->post_id,
                'start_date' => $event_start_date->format('Y-m-d'),
                'end_date' => $end_date,
                'time_display' => __('Event', 'lunar-calendar'),
                'location' => $location_info,
                'event_url' => get_permalink($row->post_id),
                'is_recurring' => $is_recurring,
                'recurrence_pattern' => $recurrence_pattern,
            ];
        }

        return $events;
    }

    /**
     * Get Solar events from JSON database
     *
     * @param int $month Solar month (1-12)
     * @param int $year Solar year
     * @return array
     */
    protected static function getSolarEvents($month, $year)
    {
        $file = self::DB_PATH . '/solar-events/' . sprintf('%02d', $month) . '.json';

        if (!file_exists($file)) {
            return [];
        }

        $content = file_get_contents($file);
        $data = json_decode($content, true);

        if (!$data || !isset($data['events'])) {
            return [];
        }

        $events = [];

        foreach ($data['events'] as $event) {
            // Calculate day based on day_rule
            $day = null;

            if (isset($event['day_rule']) && $event['day_rule'] !== 'fixed') {
                // Calculate day for weekday-based events
                $day = self::calculateWeekdayBasedDay($month, $year, $event);
            } elseif (isset($event['day'])) {
                $day = $event['day'];
            }

            if ($day === null) {
                continue;
            }

            // Calculate years ago if year is provided
            $years_ago = null;
            if (isset($event['year']) && is_numeric($event['year'])) {
                $years_ago = $year - intval($event['year']);
            }

            $events[] = [
                'day' => $day,
                'title' => $event['title'] ?? __('Event', 'lunar-calendar'),
                'description' => $event['description'] ?? '',
                'type' => $event['type'] ?? 0,
                'typeName' => $event['type_name'] ?? 'default',
                'isHoliday' => $event['is_holiday'] ?? false,
                'year' => $event['year'] ?? null,
                'yearsAgo' => $years_ago,
                'isToday' => false,
                'event_id' => null,
                'post_id' => null,
                'start_date' => sprintf('%04d-%02d-%02d', $year, $month, $day),
                'end_date' => sprintf('%04d-%02d-%02d', $year, $month, $day),
                'time_display' => '',
                'location' => '',
                'event_url' => '',
                'is_recurring' => isset($event['recurrence']) && $event['recurrence'] === 'yearly',
                'recurrence_pattern' => isset($event['recurrence']) && $event['recurrence'] === 'yearly' ? __('yearly', 'lunar-calendar') : '',
            ];
        }

        return $events;
    }

    /**
     * Get Lunar events from JSON database (converted to solar date)
     *
     * @param int $month Solar month (1-12)
     * @param int $year Solar year
     * @return array
     */
    protected static function getLunarEvents($month, $year)
    {
        $events = [];

        // Load all 12 lunar months and try to convert them to the target solar month
        // Because lunar calendar shifts each year, we need to check all lunar months
        for ($lunar_month = 1; $lunar_month <= 12; $lunar_month++) {
            $file = self::DB_PATH . '/lunar-events/' . sprintf('%02d', $lunar_month) . '.json';

            if (!file_exists($file)) {
                continue;
            }

            $content = file_get_contents($file);
            $data = json_decode($content, true);

            if (!$data || !isset($data['events'])) {
                continue;
            }

            foreach ($data['events'] as $event) {
                try {
                    $lunar_day = $event['day'] ?? 1;

                    // For lunar calendar, we need to try both current year and previous year
                    // because lunar new year can fall in Jan/Feb, causing events to span two solar years
                    // For example: Lunar year 2025 starts on 2025-01-29, so Tet falls in Jan 2025
                    // but some lunar events from lunar year 2024 can also fall in Jan 2025
                    $lunar_years_to_try = [$year, $year - 1];

                    foreach ($lunar_years_to_try as $try_year) {
                        $solar_date = self::convertLunarToSolar($lunar_day, $lunar_month, $try_year);

                        if ($solar_date) {
                            // Check if this solar date falls in our target month/year
                            if ($solar_date['year'] == $year && $solar_date['month'] == $month) {
                                // Calculate years ago if year is provided
                                $years_ago = null;
                                if (isset($event['year']) && is_numeric($event['year'])) {
                                    $years_ago = $year - intval($event['year']);
                                }

                                $events[] = [
                                    'day' => $solar_date['day'],
                                    'title' => $event['title'] ?? __('Event', 'lunar-calendar'),
                                    'description' => $event['description'] ?? '',
                                    'type' => $event['type'] ?? 0,
                                    'typeName' => $event['type_name'] ?? 'default',
                                    'isHoliday' => $event['is_holiday'] ?? false,
                                    'year' => $event['year'] ?? null,
                                    'yearsAgo' => $years_ago,
                                    'isToday' => false,
                                    'event_id' => null,
                                    'post_id' => null,
                                    'start_date' => sprintf('%04d-%02d-%02d', $solar_date['year'], $solar_date['month'], $solar_date['day']),
                                    'end_date' => sprintf('%04d-%02d-%02d', $solar_date['year'], $solar_date['month'], $solar_date['day']),
                                    'time_display' => '',
                                    'location' => '',
                                    'event_url' => '',
                                    'is_recurring' => isset($event['recurrence']) && $event['recurrence'] === 'yearly',
                                    'recurrence_pattern' => isset($event['recurrence']) && $event['recurrence'] === 'yearly' ? __('yearly', 'lunar-calendar') : '',
                                ];

                                // Found a match, no need to try other years
                                break;
                            }
                        }
                    }
                } catch (\Exception $e) {
                    // Skip invalid dates
                    continue;
                }
            }
        }

        return $events;
    }

    /**
     * Convert lunar date to solar date
     *
     * @param int $lunar_day
     * @param int $lunar_month
     * @param int $lunar_year The lunar year (e.g. 2025 lunar year)
     * @return array|null
     */
    protected static function convertLunarToSolar($lunar_day, $lunar_month, $lunar_year)
    {
        try {
            // Use jetfueltw/solarlunar-php library
            // Note: Lunar year is different from solar year
            $lunar = \Jetfuel\SolarLunar\Lunar::create($lunar_year, $lunar_month, $lunar_day, false);
            $solar = SolarLunar::lunarToSolar($lunar);

            return [
                'day' => $solar->day,
                'month' => $solar->month,
                'year' => $solar->year,
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Calculate day for weekday-based events
     *
     * Examples:
     * - second_sunday: Second Sunday of the month
     * - third_sunday: Third Sunday of the month
     * - fourth_thursday: Fourth Thursday of the month
     *
     * @param int $month
     * @param int $year
     * @param array $event
     * @return int|null
     */
    protected static function calculateWeekdayBasedDay($month, $year, $event)
    {
        if (!isset($event['weekday']) || !isset($event['week_number'])) {
            return null;
        }

        $weekday = intval($event['weekday']); // 0 = Sunday, 1 = Monday, ..., 6 = Saturday
        $week_number = intval($event['week_number']); // 1 = first, 2 = second, 3 = third, 4 = fourth

        // Get first day of the month
        $first_day = mktime(0, 0, 0, $month, 1, $year);
        $first_weekday = intval(date('w', $first_day));

        // Calculate days to add to reach the first occurrence of the target weekday
        $days_to_add = ($weekday - $first_weekday + 7) % 7;

        // Calculate the day of the nth occurrence
        $target_day = 1 + $days_to_add + (($week_number - 1) * 7);

        // Validate day is within month
        $days_in_month = intval(date('t', $first_day));
        if ($target_day > $days_in_month) {
            return null;
        }

        return $target_day;
    }

    /**
     * Remove duplicate events on same day (keep higher weight)
     *
     * @param array $events
     * @return array
     */
    protected static function removeDuplicates($events)
    {
        $unique = [];
        $seen_days = [];

        foreach ($events as $event) {
            $day = $event['day'];

            // If day not seen yet, or this event has higher weight, keep it
            if (!isset($seen_days[$day])) {
                $seen_days[$day] = true;
                $unique[] = $event;
            }
            // If we want to keep multiple events per day, comment out the above condition
            // and always add the event:
            // $unique[] = $event;
        }

        return $unique;
    }

    /**
     * Clear cache
     */
    public static function clearCache()
    {
        self::$cache = [];
    }
}

