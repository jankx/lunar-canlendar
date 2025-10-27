<?php
namespace Jankx\LunarCanlendar;

use Jankx\Gutenberg\Block;


class LunarCanlendarBlock extends Block
{
    /**
     * AJAX handler for calendar events
     * Tích hợp với Events Manager plugin
     */
    public static function ajax_calendar_events()
    {
        $month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
        $year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

        $events = [];

        // Tính toán ngày đầu và cuối tháng
        $start_date = sprintf('%04d-%02d-01', $year, $month);
        $last_day = date('t', strtotime($start_date));
        $end_date = sprintf('%04d-%02d-%02d', $year, $month, $last_day);

        // Lấy events trực tiếp từ database em_events
        $events = self::get_events_from_database($month, $year);

        wp_send_json([
            'success' => true,
            'data' => [
                'month' => sprintf('%02d', $month),
                'year' => $year,
                'events' => $events,
                'total' => count($events),
            ]
        ]);
    }

    /**
     * Phương thức backup: Lấy events trực tiếp từ database
     */
    public static function get_events_from_database($month, $year)
    {
        global $wpdb;

        // Tính toán ngày đầu và cuối tháng
        $start_date = sprintf('%04d-%02d-01 00:00:00', $year, $month);
        $last_day = date('t', strtotime($start_date));
        $end_date = sprintf('%04d-%02d-%02d 23:59:59', $year, $month, $last_day);

        // Truy vấn events từ wp-event-solution plugin (post type: etn)
        $sql = $wpdb->prepare("
            SELECT
                p.ID as post_id,
                p.post_title as event_name,
                p.post_excerpt,
                p.post_content,
                p.post_status
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

        // Map category slug sang event type
        $category_type_map = apply_filters('lunar_calendar_category_type_map', [
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

        $events = [];

        foreach ($results as $row) {
            // Lấy ngày bắt đầu từ meta field etn_start_date
            $event_start_date_str = get_post_meta($row->post_id, 'etn_start_date', true);
            if (!$event_start_date_str) {
                continue; // Bỏ qua nếu không có ngày bắt đầu
            }

            $event_start_date = new \DateTime($event_start_date_str);
            $day = intval($event_start_date->format('j'));

            // Đơn giản hóa cho lịch âm dương: chỉ hiển thị "Sự kiện"
            $time_display = 'Sự kiện';

            // Lấy category đầu tiên để xác định type (wp-event-solution dùng etn_category)
            $event_type = 'default'; // mặc định là không có category (màu xám)
            $categories = get_the_terms($row->post_id, 'etn_category');
            if (!empty($categories) && !is_wp_error($categories)) {
                $first_category = reset($categories);
                $category_slug = $first_category->slug;

                if (isset($category_type_map[$category_slug])) {
                    $event_type = $category_type_map[$category_slug];
                }
            }

            // Map type sang number cho frontend
            $type_map = apply_filters('lunar_calendar_type_number_map', [
                'default' => 0,      // mặc định - màu xám
                'national' => 1,     // màu đỏ
                'historical' => 2,   // màu xanh dương
                'international' => 3, // màu xanh lá
                'professional' => 4,  // màu tím
                'social' => 5,        // màu cam
                'memorial' => 6,      // màu nâu
                'celebration' => 7,   // màu hồng
                'cultural' => 8,      // màu cyan
                'religious' => 9,     // màu vàng
            ]);
            $type_number = isset($type_map[$event_type]) ? $type_map[$event_type] : 0;

            // Lấy năm từ custom field
            $event_year = null;
            $years_ago = null;
            $stored_year = get_post_meta($row->post_id, '_event_year', true);
            if ($stored_year && is_numeric($stored_year)) {
                $event_year = intval($stored_year);
                $years_ago = date('Y') - $event_year;
            }

            // Tạo description từ post_excerpt hoặc post_content
            $description = '';
            if (!empty($row->post_excerpt)) {
                $description = $row->post_excerpt;
            } elseif (!empty($row->post_content)) {
                $description = wp_trim_words($row->post_content, 20, '...');
            }

            // Nếu vẫn không có description, để trống (không dùng title)
            if (empty($description)) {
                $description = '';
            }

            // Thêm thông tin lịch sử vào description nếu có
            if (!empty($description) && $event_year && $years_ago > 0) {
                $description .= ' (' . $event_year . ') - ' . $years_ago . ' năm trước';
            }

            // Lấy thông tin địa điểm từ meta field etn_event_location
            $location_info = get_post_meta($row->post_id, 'etn_event_location', true) ?: '';

            // Kiểm tra recurring từ database
            $is_recurring = false;
            $recurrence_pattern = '';

            // Kiểm tra từ post meta
            $recurrence_freq = get_post_meta($row->post_id, '_event_recurrence_freq', true);
            $recurrence_interval = get_post_meta($row->post_id, '_event_recurrence_interval', true);

            if ($recurrence_freq) {
                $is_recurring = true;

                $freq_map = [
                    'daily' => 'hàng ngày',
                    'weekly' => 'hàng tuần',
                    'monthly' => 'hàng tháng',
                    'yearly' => 'hàng năm',
                ];

                if (isset($freq_map[$recurrence_freq])) {
                    $interval = $recurrence_interval ?: 1;
                    if ($interval == 1) {
                        $recurrence_pattern = $freq_map[$recurrence_freq];
                    } else {
                        $recurrence_pattern = "mỗi {$interval} " . $freq_map[$recurrence_freq];
                    }
                }
            }

            // Lấy ngày kết thúc từ meta field etn_end_date
            $event_end_date_str = get_post_meta($row->post_id, 'etn_end_date', true);
            $end_date = $event_end_date_str ?: $event_start_date_str;

            $events[] = [
                'day' => $day,
                'title' => $row->event_name ?: 'Sự kiện',
                'year' => $event_year,
                'yearsAgo' => $years_ago,
                'type' => $type_number,
                'typeName' => ucfirst($event_type),
                'description' => $description,
                'isToday' => $event_start_date->format('Y-m-d') === date('Y-m-d'),
                'isHoliday' => in_array($event_type, ['national', 'international']),
                'event_id' => $row->post_id, // Sử dụng post_id làm event_id
                'post_id' => $row->post_id,
                'start_date' => $event_start_date->format('Y-m-d'),
                'end_date' => $end_date,
                'time_display' => $time_display,
                'location' => $location_info,
                'event_url' => get_permalink($row->post_id),
                'is_recurring' => $is_recurring,
                'recurrence_pattern' => $recurrence_pattern,
            ];
        }

        return $events;
    }

    /**
     * Debug method: Kiểm tra cấu trúc database (chỉ dùng khi debug)
     */
    public static function debug_database_structure()
    {
        global $wpdb;

        $table_prefix = $wpdb->prefix;
        $events_table = $table_prefix . 'em_events';
        $locations_table = $table_prefix . 'em_locations';

        $debug_info = [
            'table_prefix' => $table_prefix,
            'events_table' => $events_table,
            'locations_table' => $locations_table,
            'events_table_exists' => $wpdb->get_var("SHOW TABLES LIKE '$events_table'") !== null,
            'locations_table_exists' => $wpdb->get_var("SHOW TABLES LIKE '$locations_table'") !== null,
        ];

        // Kiểm tra có events không
        if ($debug_info['events_table_exists']) {
            $debug_info['events_count'] = $wpdb->get_var("SELECT COUNT(*) FROM $events_table");
            $debug_info['sample_event'] = $wpdb->get_row("SELECT * FROM $events_table LIMIT 1");
        }

        return $debug_info;
    }

    /**
     * Register AJAX handler
     */
    public static function register_ajax_handler()
    {
        add_action('wp_ajax_jankx_lunar_calendar_events', [__CLASS__, 'ajax_calendar_events']);
        add_action('wp_ajax_nopriv_jankx_lunar_calendar_events', [__CLASS__, 'ajax_calendar_events']);

        // Debug endpoint (chỉ cho admin)
        if (current_user_can('manage_options')) {
            add_action('wp_ajax_jankx_lunar_calendar_debug', [__CLASS__, 'ajax_debug_database']);
        }
    }

    /**
     * Debug AJAX handler
     */
    public static function ajax_debug_database()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
            return;
        }

        $debug_info = self::debug_database_structure();
        wp_send_json_success($debug_info);
    }

    protected $blockId = 'jankx/lunar-calendar';

    public function init()
    {
        add_action('wp_enqueue_scripts', function () {
            $assets_url = get_stylesheet_directory_uri() . '/vendor/jankx/lunar-canlendar/assets';

            // Third-party deps - Local files
            wp_enqueue_script(
                'moment',
                $assets_url . '/js/moment.min.js',
                array(),
                '2.29.4',
                true
            );
            wp_enqueue_script(
                'moment-locale-vi',
                $assets_url . '/js/vi.min.js',
                array('moment'),
                '2.29.4',
                true
            );
            wp_enqueue_script(
                'lunar-date-vi',
                $assets_url . '/js/lunar_date_vi.min.js',
                array(),
                '2.0.1',
                true
            );

            // FontAwesome CSS
            wp_enqueue_style(
                'font-awesome',
                $assets_url . '/fontawesome/all.min.css',
                array(),
                '6.0.0'
            );

            // Block frontend
            wp_enqueue_script(
                'jankx-lunar-calendar-frontend',
                get_stylesheet_directory_uri() . '/vendor/jankx/lunar-canlendar/blocks/lunar-calendar/build/frontend.js',
                array('moment', 'moment-locale-vi', 'lunar-date-vi'),
                '1.0.0',
                true
            );
        });
    }


    /**
     * Sanitize icon HTML allowing SVG and common icon tags
     */
    private function sanitize_icon_html($html)
    {
        if (empty($html)) {
            return '';
        }

        // Define allowed HTML tags and attributes for icons
        $allowed_tags = [
            'svg' => [
                'xmlns' => true,
                'viewbox' => true,
                'width' => true,
                'height' => true,
                'fill' => true,
                'class' => true,
                'aria-hidden' => true,
                'role' => true,
                'focusable' => true,
            ],
            'path' => [
                'd' => true,
                'fill' => true,
                'stroke' => true,
                'stroke-width' => true,
                'stroke-linecap' => true,
                'stroke-linejoin' => true,
                'class' => true,
            ],
            'circle' => [
                'cx' => true,
                'cy' => true,
                'r' => true,
                'fill' => true,
                'stroke' => true,
                'stroke-width' => true,
                'class' => true,
            ],
            'rect' => [
                'x' => true,
                'y' => true,
                'width' => true,
                'height' => true,
                'fill' => true,
                'stroke' => true,
                'stroke-width' => true,
                'rx' => true,
                'ry' => true,
                'class' => true,
            ],
            'polygon' => [
                'points' => true,
                'fill' => true,
                'stroke' => true,
                'stroke-width' => true,
                'class' => true,
            ],
            'polyline' => [
                'points' => true,
                'fill' => true,
                'stroke' => true,
                'stroke-width' => true,
                'class' => true,
            ],
            'line' => [
                'x1' => true,
                'y1' => true,
                'x2' => true,
                'y2' => true,
                'stroke' => true,
                'stroke-width' => true,
                'class' => true,
            ],
            'ellipse' => [
                'cx' => true,
                'cy' => true,
                'rx' => true,
                'ry' => true,
                'fill' => true,
                'stroke' => true,
                'stroke-width' => true,
                'class' => true,
            ],
            'g' => [
                'fill' => true,
                'stroke' => true,
                'stroke-width' => true,
                'transform' => true,
                'class' => true,
            ],
            'defs' => [],
            'clippath' => [
                'id' => true,
            ],
            'mask' => [
                'id' => true,
            ],
            'i' => [
                'class' => true,
                'aria-hidden' => true,
            ],
            'span' => [
                'class' => true,
                'aria-hidden' => true,
            ],
            'img' => [
                'src' => true,
                'alt' => true,
                'width' => true,
                'height' => true,
                'class' => true,
            ],
        ];

        return wp_kses($html, $allowed_tags);
    }

    public function render($attributes = [], $content = '', $block = null)
    {
        // Tạo endpoint động cho JS
        $ajax_url = admin_url('admin-ajax.php');
        $api_url = add_query_arg([
            'action' => 'jankx_lunar_calendar_events',
        ], $ajax_url);

        // Get block attributes
        $show_today_button = isset($attributes['showTodayButton']) ? (bool) $attributes['showTodayButton'] : false;
        $gregorian_icon_html = $this->sanitize_icon_html(isset($attributes['gregorianIconHtml']) ? $attributes['gregorianIconHtml'] : '');
        $lunar_icon_html = $this->sanitize_icon_html(isset($attributes['lunarIconHtml']) ? $attributes['lunarIconHtml'] : '');

        // Only output the script if not an AJAX request or REST API request
        $is_rest_request = defined('REST_REQUEST') && REST_REQUEST;
        $is_ajax_request = defined('DOING_AJAX') && DOING_AJAX;
        $should_output_script = !$is_rest_request && !$is_ajax_request && !is_admin();
        
        if ($should_output_script) {
            echo '<script>window.lunarCalendarApiUrl = ' . json_encode($api_url) . ';</script>';
        }
        ob_start();
        ?>
        <div class="lunar-calendar-container">

            <div class="lunar-current-date-infos">
                <!-- Current Date Display with prev/next day buttons -->
                <div class="lunar-current-date-section">
                    <div class="calendar-controls">
                        <button class="lunar-date-nav-btn" id="prev-day-btn" title="Ngày trước">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <button class="lunar-date-nav-btn" id="next-day-btn" title="Ngày tiếp theo">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                    <div class="lunar-date-column lunar-date-column-gregorian">
                        <div class="lunar-date-label">
                            <?php if (!empty($gregorian_icon_html)): ?>
                                <?php echo $gregorian_icon_html; ?>
                            <?php else: ?>
                                <i class="fas fa-calendar-alt"></i>
                            <?php endif; ?>
                            Dương lịch
                        </div>
                        <div class="lunar-date-number" id="current-gregorian-day"><?php echo date('d'); ?></div>
                        <div class="lunar-date-month-year" id="current-gregorian-month-year">Tháng <?php echo date('m'); ?> năm <?php echo date('Y'); ?></div>
                        <div class="lunar-date-day" id="current-gregorian-day-name"><?php 
                            $day_names_vi = ['Chủ nhật', 'Thứ hai', 'Thứ ba', 'Thứ tư', 'Thứ năm', 'Thứ sáu', 'Thứ bảy'];
                            echo $day_names_vi[date('w')];
                        ?></div>
                    </div>
                    <div class="lunar-date-column lunar-date-column-lunar">
                        <div class="lunar-date-label">
                            <?php if (!empty($lunar_icon_html)): ?>
                                <?php echo $lunar_icon_html; ?>
                            <?php else: ?>
                                <i class="fas fa-moon"></i>
                            <?php endif; ?>
                            Âm lịch
                        </div>
                        <div class="lunar-date-number" id="current-lunar-day">15</div>
                        <div class="lunar-date-month-year" id="current-lunar-month-year">Tháng 06 năm Ất Tỵ</div>
                        <div class="lunar-info" id="current-lunar-details">Ngày Kỷ Dậu - Tháng Quý Mùi</div>
                    </div>
                </div>

                <!-- Holiday Information -->
                <div class="lunar-holiday-info">
                    <div class="lunar-holiday-title">Thông tin ngày lễ hôm nay</div>
                    <div class="lunar-holiday-content" id="holiday-info">Không có</div>
                </div>

                <!-- Loading Indicator -->
                <div id="loading-indicator" class="lunar-loading-indicator" style="display: none;">
                    <div class="lunar-loading-spinner"></div>
                    <span class="lunar-loading-text">Đang tải dữ liệu...</span>
                </div>
            </div>

            <!-- Navigation Bar -->
            <div class="lunar-calendar-nav">
                <div class="current-month-navigation">
                    <button class="lunar-nav-arrow" id="prev-month">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <div class="lunar-current-month-year" id="current-month-year">Tháng <?php echo date('n'); ?> - <?php echo date('Y'); ?></div>
                    <button class="lunar-nav-arrow" id="next-month">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
                <div class="lunar-month-year-selectors">
                    <select id="month-selector">
                        <?php
                        $current_month = date('n');
                        for ($m = 1; $m <= 12; $m++) {
                            printf(
                                '<option value="%d"%s>Tháng %d</option>',
                                $m,
                                $m == $current_month ? ' selected' : '',
                                $m
                            );
                        }
                        ?>
                    </select>
                    <select id="year-selector">
                        <?php
                        $current_year = date('Y');
                        for ($y = $current_year - 2; $y <= $current_year + 2; $y++) {
                            printf(
                                '<option value="%d"%s>%d</option>',
                                $y,
                                $y == $current_year ? ' selected' : '',
                                $y
                            );
                        }
                        ?>
                    </select>
                    <button class="lunar-view-btn" id="view-btn">Xem</button>
                    <?php if ($show_today_button): ?>
                        <button class="lunar-today-btn" id="today-btn">Hôm nay</button>
                    <?php endif; ?>
                </div>
            </div>
            <!-- Calendar Grid -->
            <div class="lunar-calendar-grid">
                <div class="lunar-weekdays">
                    <div class="lunar-weekday">Thứ hai</div>
                    <div class="lunar-weekday">Thứ ba</div>
                    <div class="lunar-weekday">Thứ tư</div>
                    <div class="lunar-weekday">Thứ năm</div>
                    <div class="lunar-weekday">Thứ sáu</div>
                    <div class="lunar-weekday">Thứ bảy</div>
                    <div class="lunar-weekday">Chủ nhật</div>
                </div>
                <div class="lunar-calendar-days" id="calendar-days">
                    <!-- Calendar days will be generated by JavaScript -->
                </div>
            </div>
        </div>

        <script>
            // Vietnamese day names
            const dayNames = ['Chủ nhật', 'Thứ hai', 'Thứ ba', 'Thứ tư', 'Thứ năm', 'Thứ sáu', 'Thứ bảy'];

            // Calendar Events Data Structure
            let calendarEvents = {
                prev: { month: '', events: [] },
                current: { month: '', events: [] },
                next: { month: '', events: [] }
            };

            // Selected holidays cache - stores events by date key (YYYY-MM-DD)
            let selectedHolidays = {};

            // Calendar configuration options
            window.calendarConfig = {
                showTodayButton: <?php echo $show_today_button ? 'true' : 'false'; ?>,  // Set to false to hide today button
                todayButtonText: 'Hôm nay',
                maxHolidayInfoItems: 1,  // Maximum number of events to show in holiday info section
                showAdditionalEventsMessage: false  // Set to true to show "Còn X sự kiện khác" message
            };
            const calendarConfig = window.calendarConfig;

            // Function to update calendar configuration
            // Usage:
            // updateCalendarConfig({ maxHolidayInfoItems: 3 })
            // updateCalendarConfig({ showAdditionalEventsMessage: true })
            // updateCalendarConfig({ maxHolidayInfoItems: 2, showAdditionalEventsMessage: true })
            function updateCalendarConfig(newConfig) {
                Object.assign(calendarConfig, newConfig);
                // Refresh holiday info display if calendar is already initialized
                if (typeof window.lunarCalendarInstance !== 'undefined') {
                    window.lunarCalendarInstance.updateHolidayInfo();
                }
            }

            // Function to get current calendar configuration
            function getCalendarConfig() {
                return { ...calendarConfig };
            }

            // Expose functions globally for external access
            window.updateCalendarConfig = updateCalendarConfig;
            window.getCalendarConfig = getCalendarConfig;

            // Fallback data generator (chỉ dùng khi API lỗi)
            function generateFallbackEvents(month, year) {
                // Trả về mảng rỗng khi không có sự kiện
                return [];
            }

            // AJAX fetch function for calendar events
            async function fetchCalendarEvents(month, year, showLoading = true) {
                try {
                    // Show loading state only if requested
                    if (showLoading) {
                        showLoadingState();
                    }

                    // Build API URL with full params (append to existing URL)
                    const baseUrl = window.lunarCalendarApiUrl || '';
                    const separator = baseUrl.includes('?') ? '&' : '?';
                    const apiUrl = `${baseUrl}${separator}month=${month}&year=${year}&format=json&locale=vi&timezone=${encodeURIComponent(Intl.DateTimeFormat().resolvedOptions().timeZone)}`;

                    // Make real AJAX request
                    const response = await fetch(apiUrl, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                        },
                        mode: 'cors'
                    });

                    // Check if response is ok
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    // Parse JSON response
                    const data = await response.json();

                    // Hide loading state only if it was shown
                    if (showLoading) {
                        hideLoadingState();
                    }


                    // Return events from API response
                    if (data.success && data.data && data.data.events) {
                        return data.data.events;
                    } else {
                        return [];
                    }

                } catch (error) {
                    // Hide loading state only if it was shown
                    if (showLoading) {
                        hideLoadingState();
                    }

                    // Fallback to fallback data on error
                    return generateFallbackEvents(month, year);
                }
            }

            // Loading state management
            function showLoadingState() {
                const loadingIndicator = document.getElementById('loading-indicator');
                if (loadingIndicator) {
                    loadingIndicator.style.display = 'flex';
                }

                // Disable navigation buttons
                disableNavigationButtons();
            }

            function hideLoadingState() {
                const loadingIndicator = document.getElementById('loading-indicator');
                if (loadingIndicator) {
                    loadingIndicator.style.display = 'none';
                }

                // Re-enable navigation buttons
                enableNavigationButtons();
            }

            // Disable navigation buttons
            function disableNavigationButtons() {
                const navButtons = document.querySelectorAll('#prev-month, #next-month');
                navButtons.forEach(btn => {
                    btn.disabled = true;
                    btn.style.opacity = '0.6';
                    btn.style.cursor = 'not-allowed';
                    btn.style.pointerEvents = 'none';
                });
            }

            // Enable navigation buttons
            function enableNavigationButtons() {
                const navButtons = document.querySelectorAll('#prev-month, #next-month');
                navButtons.forEach(btn => {
                    btn.disabled = false;
                    btn.style.opacity = '1';
                    btn.style.cursor = 'pointer';
                    btn.style.pointerEvents = 'auto';
                });
            }

            // Show full page loading overlay
            function showPageLoading() {
                const pageLoadingOverlay = document.getElementById('page-loading-overlay');
                const calendarContainer = document.querySelector('.lunar-calendar-container');

                if (pageLoadingOverlay) {
                    pageLoadingOverlay.style.display = 'flex';
                }

                if (calendarContainer) {
                    calendarContainer.classList.add('loading');
                }
            }

            // Hide full page loading overlay
            function hidePageLoading() {
                const pageLoadingOverlay = document.getElementById('page-loading-overlay');
                const calendarContainer = document.querySelector('.lunar-calendar-container');

                if (pageLoadingOverlay) {
                    pageLoadingOverlay.style.display = 'none';
                }

                if (calendarContainer) {
                    calendarContainer.classList.remove('loading');
                }
            }

            // Initialize calendar events data
            async function initializeCalendarEvents(currentMonth, currentYear) {
                try {
                    const [prevEvents, currentEvents, nextEvents] = await Promise.all([
                        fetchCalendarEvents(currentMonth - 1, currentYear, true),
                        fetchCalendarEvents(currentMonth, currentYear, true),
                        fetchCalendarEvents(currentMonth + 1, currentYear, true)
                    ]);

                    // Calculate month identifiers
                    const prevMonthId = formatMonthId(currentMonth - 1, currentYear);
                    const currentMonthId = formatMonthId(currentMonth, currentYear);
                    const nextMonthId = formatMonthId(currentMonth + 1, currentYear);

                    calendarEvents = {
                        prev: { month: prevMonthId, events: prevEvents },
                        current: { month: currentMonthId, events: currentEvents },
                        next: { month: nextMonthId, events: nextEvents }
                    };

                    // Cache events by date
                    cacheEventsByDate(prevEvents, currentMonth - 1, currentYear);
                    cacheEventsByDate(currentEvents, currentMonth, currentYear);
                    cacheEventsByDate(nextEvents, currentMonth + 1, currentYear);

                    return calendarEvents;
                } catch (error) {
                    return {
                        prev: { month: '', events: [] },
                        current: { month: '', events: [] },
                        next: { month: '', events: [] }
                    };
                }
            }

            // Format month identifier as yy-mm
            function formatMonthId(month, year) {
                const adjustedMonth = month <= 0 ? month + 12 : month > 12 ? month - 12 : month;
                const adjustedYear = month <= 0 ? year - 1 : month > 12 ? year + 1 : year;

                const yy = adjustedYear.toString().slice(-2);
                const mm = adjustedMonth.toString().padStart(2, '0');

                return `${yy}-${mm}`;
            }

            // Debug function to display current data structure
            function debugCalendarData() {
                // Debug function removed - no console.log
            }

            // Check if selected month is within current range
            function isMonthInCurrentRange(month, year) {
                const selectedMonthId = formatMonthId(month, year);
                const currentRange = [
                    calendarEvents.prev.month,
                    calendarEvents.current.month,
                    calendarEvents.next.month
                ];

                return currentRange.includes(selectedMonthId);
            }

            // Cache events by date key (YYYY-MM-DD)
            function cacheEventsByDate(events, month, year) {
                events.forEach(event => {
                    const dateKey = `${year}-${month.toString().padStart(2, '0')}-${event.day.toString().padStart(2, '0')}`;
                    selectedHolidays[dateKey] = event;
                });
            }

            // Get events for a specific date
            function getEventsForDate(date) {
                const dateKey = date.format('YYYY-MM-DD');
                return selectedHolidays[dateKey] || null;
            }

            // Clear old cached events (keep only current 3 months)
            function clearOldCachedEvents() {
                const currentDate = moment();
                const threeMonthsAgo = currentDate.clone().subtract(3, 'months');
                const threeMonthsFromNow = currentDate.clone().add(3, 'months');

                Object.keys(selectedHolidays).forEach(dateKey => {
                    const date = moment(dateKey);
                    if (date.isBefore(threeMonthsAgo) || date.isAfter(threeMonthsFromNow)) {
                        delete selectedHolidays[dateKey];
                    }
                });
            }

            // Test API endpoint
            async function testAPI() {
                try {
                    const testUrl = (window.lunarCalendarApiUrl || '') + '&month=8&year=2025';

                    const response = await fetch(testUrl, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                        },
                        mode: 'cors'
                    });

                    if (response.ok) {
                        const data = await response.json();
                        return true;
                    } else {
                        return false;
                    }
                } catch (error) {
                    return false;
                }
            }


            // Vietnamese month names
            const monthNames = [
                'Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6',
                'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'
            ];

            // Vietnamese zodiac years
            const zodiacYears = [
                'Giáp Tý', 'Ất Sửu', 'Bính Dần', 'Đinh Mão', 'Mậu Thìn', 'Kỷ Tỵ',
                'Canh Ngọ', 'Tân Mùi', 'Nhâm Thân', 'Quý Dậu', 'Giáp Tuất', 'Ất Hợi'
            ];

            // Vietnamese zodiac months
            const zodiacMonths = [
                'Giáp Tý', 'Ất Sửu', 'Bính Dần', 'Đinh Mão', 'Mậu Thìn', 'Kỷ Tỵ',
                'Canh Ngọ', 'Tân Mùi', 'Nhâm Thân', 'Quý Dậu', 'Giáp Tuất', 'Ất Hợi'
            ];

            // Vietnamese zodiac days
            const zodiacDays = [
                'Giáp Tý', 'Ất Sửu', 'Bính Dần', 'Đinh Mão', 'Mậu Thìn', 'Kỷ Tỵ',
                'Canh Ngọ', 'Tân Mùi', 'Nhâm Thân', 'Quý Dậu', 'Giáp Tuất', 'Ất Hợi'
            ];

            // Vietnamese heavenly stems
            const heavenlyStems = ['Giáp', 'Ất', 'Bính', 'Đinh', 'Mậu', 'Kỷ', 'Canh', 'Tân', 'Nhâm', 'Quý'];

            // Vietnamese earthly branches
            const earthlyBranches = ['Tý', 'Sửu', 'Dần', 'Mão', 'Thìn', 'Tỵ', 'Ngọ', 'Mùi', 'Thân', 'Dậu', 'Tuất', 'Hợi'];

            class LunarCalendar {
                constructor() {
                    this.currentDate = moment();
                    this.selectedDate = moment(); // Start with today selected
                    this.isNavigating = false;
                    this.init();
                }

                async init() {
                    // Show full page loading
                    showPageLoading();

                    try {
                        // Test API endpoint first
                        const apiWorking = await testAPI();

                        // Initialize calendar events data
                        await this.initializeEvents();

                        this.setupEventListeners();
                        this.setupTodayButton();
                        this.updateCurrentDateDisplay();
                        this.generateCalendar();
                    } finally {
                        // Hide full page loading
                        hidePageLoading();
                    }
                }

                async initializeEvents() {
                    const currentMonth = this.currentDate.month() + 1;
                    const currentYear = this.currentDate.year();

                    await initializeCalendarEvents(currentMonth, currentYear);
                }

                setupEventListeners() {
                    // Navigation arrows with optimized event loading
                    document.getElementById('prev-month').addEventListener('click', async (e) => {
                        if (this.isNavigating || e.target.disabled) return;
                        this.isNavigating = true;
                        try {
                            // Giảm tháng hiện tại, giữ nguyên selectedDate
                            const prevMonth = this.currentDate.clone().subtract(1, 'month');
                            this.currentDate = prevMonth;
                            await this.fetchAndUpdateAllMonths(this.currentDate.month() + 1, this.currentDate.year());
                            // KHÔNG cập nhật selectedDate, KHÔNG gọi updateCurrentDateDisplay
                        } finally {
                            this.isNavigating = false;
                        }
                    });

                    document.getElementById('next-month').addEventListener('click', async (e) => {
                        if (this.isNavigating || e.target.disabled) return;
                        this.isNavigating = true;
                        try {
                            // Tăng tháng hiện tại, giữ nguyên selectedDate
                            const nextMonth = this.currentDate.clone().add(1, 'month');
                            this.currentDate = nextMonth;
                            await this.fetchAndUpdateAllMonths(this.currentDate.month() + 1, this.currentDate.year());
                            // KHÔNG cập nhật selectedDate, KHÔNG gọi updateCurrentDateDisplay
                        } finally {
                            this.isNavigating = false;
                        }
                    });

                    // Prev/Next day buttons
                    const prevBtn = document.getElementById('prev-day-btn');
                    const nextBtn = document.getElementById('next-day-btn');
                    if (prevBtn && nextBtn) {
                        prevBtn.addEventListener('click', () => {
                            this.selectedDate = this.selectedDate.clone().subtract(1, 'day');
                            if (!this.selectedDate.isSame(this.currentDate, 'month')) {
                                this.currentDate = this.selectedDate.clone();
                                this.fetchAndUpdateAllMonths(this.currentDate.month() + 1, this.currentDate.year());
                            } else {
                                this.updateCurrentDateDisplay();
                                this.generateCalendar();
                            }
                        });
                        nextBtn.addEventListener('click', () => {
                            this.selectedDate = this.selectedDate.clone().add(1, 'day');
                            if (!this.selectedDate.isSame(this.currentDate, 'month')) {
                                this.currentDate = this.selectedDate.clone();
                                this.fetchAndUpdateAllMonths(this.currentDate.month() + 1, this.currentDate.year());
                            } else {
                                this.updateCurrentDateDisplay();
                                this.generateCalendar();
                            }
                        });
                    }

                    // Month/Year selectors
                    document.getElementById('view-btn').addEventListener('click', async () => {
                        if (this.isNavigating) return;
                        this.isNavigating = true;
                        try {
                            const month = parseInt(document.getElementById('month-selector').value);
                            const year = parseInt(document.getElementById('year-selector').value);
                            const selectedDate = moment([year, month - 1]);
                            // Check if selected date is outside current range
                            const selectedMonthId = formatMonthId(month, year);
                            // ...existing code...
                            if (!isMonthInCurrentRange(month, year)) {
                                await this.fetchAndUpdateAllMonths(month, year);
                            } else {
                                this.currentDate = selectedDate;
                                this.generateCalendar();
                                this.updateCurrentDateDisplay();
                                this.updateHolidayInfo();
                            }
                        } finally {
                            this.isNavigating = false;
                        }
                    });

                    // Calendar day clicks
                    document.getElementById('calendar-days').addEventListener('click', (e) => {
                        const calendarDay = e.target.closest('.lunar-calendar-day');
                        if (calendarDay) {
                            if (!calendarDay.classList.contains('other-month')) {
                                const day = parseInt(calendarDay.dataset.day);
                                this.selectedDate = moment([this.currentDate.year(), this.currentDate.month(), day]);
                                this.updateCurrentDateDisplay();
                                this.generateCalendar();
                            }
                        }
                    });
                }

                setupTodayButton() {
                    const todayBtn = document.getElementById('today-btn');
                    if (todayBtn) {
                        // Show/hide button based on config
                        if (!calendarConfig.showTodayButton) {
                            todayBtn.style.display = 'none';
                            return;
                        }

                        // Set button text
                        todayBtn.textContent = calendarConfig.todayButtonText;

                        // Add click event
                        todayBtn.addEventListener('click', () => {
                            this.goToToday();
                        });
                    }
                }

                goToToday() {
                    const today = moment();
                    const todayMonth = today.month() + 1;
                    const todayYear = today.year();

                    // Check if today is in current range
                    if (isMonthInCurrentRange(todayMonth, todayYear)) {
                        // Today is in current range, just update selected date
                        this.selectedDate = today.clone();
                        this.updateCurrentDateDisplay();
                        this.generateCalendar();
                    } else {
                        // Today is outside current range, fetch all 3 months
                        this.fetchAndUpdateAllMonths(todayMonth, todayYear);
                    }
                }

                updateCurrentDateDisplay() {
                    // Show selected date from calendar grid
                    const selectedDate = this.selectedDate;
                    const gregorianDay = selectedDate.date();
                    const gregorianMonth = selectedDate.month() + 1;
                    const gregorianYear = selectedDate.year();
                    const dayName = dayNames[selectedDate.day()];

                    // Update Gregorian date display
                    document.getElementById('current-gregorian-day').textContent = gregorianDay.toString().padStart(2, '0');
                    document.getElementById('current-gregorian-month-year').textContent =
                        `Tháng ${gregorianMonth.toString().padStart(2, '0')} năm ${gregorianYear}`;
                    document.getElementById('current-gregorian-day-name').textContent = dayName;

                    // Calculate lunar date for selected date
                    const lunarDate = this.getLunarDate(selectedDate);

                    // Update lunar date display
                    document.getElementById('current-lunar-day').textContent = lunarDate.day;
                    document.getElementById('current-lunar-month-year').textContent =
                        `Tháng ${lunarDate.month} năm ${lunarDate.yearName}`;
                    document.getElementById('current-lunar-details').textContent =
                        `Ngày ${lunarDate.dayName} - Tháng ${lunarDate.monthName}`;

                    // Update holiday info for selected date
                    this.updateHolidayInfo();
                }

                getLunarDate(gregorianDate) {
                    try {
                        // Sử dụng thư viện lịch âm Việt Nam chính xác
                        if (typeof window._calendar !== 'undefined' && window._calendar.SolarDate && window._calendar.LunarDate) {
                            const date = gregorianDate.toDate();
                            const year = gregorianDate.year();
                            const month = gregorianDate.month() + 1;
                            const day = gregorianDate.date();

                            // Tạo SolarDate từ ngày dương lịch
                            const solarDate = new window._calendar.SolarDate({
                                day: day,
                                month: month,
                                year: year
                            });

                            // Chuyển đổi sang LunarDate
                            const lunarDate = solarDate.toLunarDate();

                            // Khởi tạo để lấy thông tin đầy đủ
                            lunarDate.init();

                            return {
                                day: lunarDate.day,
                                month: lunarDate.month,
                                year: lunarDate.year,
                                yearName: lunarDate.getYearName ? lunarDate.getYearName() : zodiacYears[(year - 4) % 60 % 12],
                                dayName: lunarDate.getDayName ? lunarDate.getDayName() : zodiacDays[(lunarDate.day - 1) % 12],
                                monthName: lunarDate.getMonthName ? lunarDate.getMonthName() : zodiacMonths[(lunarDate.month - 1) % 12],
                                phase: 'Lunar',
                                age: 0,
                                agePercent: 0,
                                isWaxing: false,
                                isWaning: false,
                                lunationNumber: 0,
                                julianDay: lunarDate.jd || 0,
                                isLeapMonth: lunarDate.leap_month || false,
                                isLeapYear: lunarDate.leap_year || false
                            };
                        } else {
                            throw new Error('Lunar date library not loaded');
                        }
                    } catch (error) {

                        // Fallback calculation với thuật toán đơn giản
                        const year = gregorianDate.year();
                        const month = gregorianDate.month() + 1;
                        const day = gregorianDate.date();

                        // Tính toán lịch âm đơn giản (không chính xác 100%)
                        const lunarDay = Math.max(1, Math.min(30, day - 15 + Math.floor(Math.random() * 3)));
                        const lunarMonth = month;

                        const yearIndex = (year - 4) % 60;
                        const monthIndex = (lunarMonth - 1) % 12;
                        const dayIndex = (lunarDay - 1) % 12;

                        return {
                            day: lunarDay,
                            month: lunarMonth,
                            year: year,
                            yearName: zodiacYears[yearIndex % 12],
                            dayName: zodiacDays[dayIndex],
                            monthName: zodiacMonths[monthIndex],
                            phase: 'Unknown',
                            age: 0,
                            agePercent: 0,
                            isWaxing: false,
                            isWaning: false,
                            lunationNumber: 0,
                            julianDay: 0,
                            isLeapMonth: false,
                            isLeapYear: false
                        };
                    }
                }

                updateHolidayInfo() {
                    // Get all events for the selected date
                    const selectedDate = this.selectedDate;
                    const selectedDay = selectedDate.date();

                    // Get events from current month's data
                    const currentEvents = calendarEvents.current?.events || [];
                    const dayEvents = currentEvents.filter(event => event.day === selectedDay);

                    let holidayHTML = '';
                    if (dayEvents.length > 0) {
                        // Limit the number of events shown based on configuration
                        const maxItems = calendarConfig.maxHolidayInfoItems || 1;
                        const eventsToShow = dayEvents.slice(0, maxItems);

                        // Show limited events for the selected day
                        eventsToShow.forEach((event, index) => {
                            const eventTypeClass = `event-type-${event.type || 1}`;
                            const eventTypeName = event.typeName || 'event';

                            // Tạo thông tin chi tiết
                            let eventDetails = '';

                            // Thông tin thời gian
                            if (event.time_display) {
                                eventDetails += `<div class="event-time"><i class="fas fa-clock"></i> ${event.time_display}</div>`;
                            }

                            // Thông tin địa điểm
                            if (event.location) {
                                const locationText = typeof event.location === 'object'
                                    ? (event.location.name || event.location.address || JSON.stringify(event.location))
                                    : event.location;
                                eventDetails += `<div class="event-location"><i class="fas fa-map-marker-alt"></i> ${locationText}</div>`;
                            }

                            // Thông tin năm lịch sử
                            if (event.year && event.yearsAgo) {
                                eventDetails += `<div class="event-history"><i class="fas fa-history"></i> ${event.year} (${event.yearsAgo} năm trước)</div>`;
                            }

                            // Thông tin recurring
                            if (event.is_recurring && event.recurrence_pattern) {
                                eventDetails += `<div class="event-recurrence"><i class="fas fa-repeat"></i> ${event.recurrence_pattern}</div>`;
                            }

                            // Link đến trang sự kiện
                            let eventLink = '';
                            if (event.event_url) {
                                eventLink = `<div class="event-link"><a href="${event.event_url}" target="_blank"><i class="fas fa-external-link-alt"></i> Xem chi tiết</a></div>`;
                            }

                            // Only show description if it exists and is different from title
                            let descriptionHTML = '';
                            if (event.description && event.description.trim() !== '' && event.description !== event.title) {
                                descriptionHTML = `<div class="holiday-event-description">${event.description}</div>`;
                            }

                            holidayHTML += `
                            <div class="holiday-event-item ${eventTypeClass}">
                                <div class="holiday-event-title">${event.title}</div>
                                ${descriptionHTML}
                                ${eventDetails}
                                <div class="holiday-event-type">${eventTypeName}</div>
                                ${eventLink}
                            </div>
                        `;
                        });

                        // Show additional events count if there are more events than displayed
                        if (dayEvents.length > maxItems && calendarConfig.showAdditionalEventsMessage) {
                            const remainingCount = dayEvents.length - maxItems;
                            holidayHTML += `<div class="additional-events-info">
                                <i class="fas fa-info-circle"></i>
                                Còn ${remainingCount} sự kiện khác trong ngày này
                            </div>`;
                        }
                    } else {
                        // Default message when no events for selected date
                        holidayHTML = '<div class="no-events">Không có sự kiện nào</div>';
                    }

                    document.getElementById('holiday-info').innerHTML = holidayHTML;
                }

                // Navigate to next month with optimized data loading
                async navigateToNextMonth() {
                    // Disable navigation buttons during fetch
                    disableNavigationButtons();

                    try {
                        const currentDate = moment(this.currentDate);
                        const nextMonth = currentDate.clone().add(1, 'month');

                        // Update data structure: prev = current, current = next, next = fetch(next+1)
                        calendarEvents.prev = { ...calendarEvents.current };
                        calendarEvents.current = { ...calendarEvents.next };

                        // Fetch next month data in background (no loading indicator)
                        const nextNextEvents = await fetchCalendarEvents(nextMonth.month() + 1, nextMonth.year(), false);
                        const nextNextMonthId = formatMonthId(nextMonth.month() + 1, nextMonth.year());
                        calendarEvents.next = { month: nextNextMonthId, events: nextNextEvents };

                        // Cache new events
                        cacheEventsByDate(nextNextEvents, nextMonth.month() + 1, nextMonth.year());

                        // Update calendar display
                        this.currentDate = nextMonth;
                        this.generateCalendar();
                        this.updateCurrentDateDisplay();
                        this.updateHolidayInfo();
                    } finally {
                        // Re-enable navigation buttons after fetch
                        enableNavigationButtons();
                    }
                }

                // Navigate to previous month with optimized data loading
                async navigateToPrevMonth() {
                    // Disable navigation buttons during fetch
                    disableNavigationButtons();

                    try {
                        const currentDate = moment(this.currentDate);
                        const prevMonth = currentDate.clone().subtract(1, 'month');

                        // Update data structure: next = current, current = prev, prev = fetch(prev-1)
                        calendarEvents.next = { ...calendarEvents.current };
                        calendarEvents.current = { ...calendarEvents.prev };

                        // Fetch previous month data in background (no loading indicator)
                        const prevPrevEvents = await fetchCalendarEvents(prevMonth.month() - 1, prevMonth.year(), false);
                        const prevPrevMonthId = formatMonthId(prevMonth.month() - 1, prevMonth.year());
                        calendarEvents.prev = { month: prevPrevMonthId, events: prevPrevEvents };

                        // Cache new events
                        cacheEventsByDate(prevPrevEvents, prevMonth.month() - 1, prevMonth.year());

                        // Update calendar display
                        this.currentDate = prevMonth;
                        this.generateCalendar();
                        this.updateCurrentDateDisplay();
                        this.updateHolidayInfo();
                    } finally {
                        // Re-enable navigation buttons after fetch
                        enableNavigationButtons();
                    }
                }

                // Fetch and update all 3 months when jumping to a date outside current range
                async fetchAndUpdateAllMonths(month, year) {
                    // Disable navigation buttons during fetch
                    disableNavigationButtons();

                    try {
                        // Fetch all 3 months in parallel (show loading for this case)
                        const [prevEvents, currentEvents, nextEvents] = await Promise.all([
                            fetchCalendarEvents(month - 1, year, true),
                            fetchCalendarEvents(month, year, true),
                            fetchCalendarEvents(month + 1, year, true)
                        ]);

                        // Calculate month identifiers
                        const prevMonthId = formatMonthId(month - 1, year);
                        const currentMonthId = formatMonthId(month, year);
                        const nextMonthId = formatMonthId(month + 1, year);

                        // Update calendar events data
                        calendarEvents = {
                            prev: { month: prevMonthId, events: prevEvents },
                            current: { month: currentMonthId, events: currentEvents },
                            next: { month: nextMonthId, events: nextEvents }
                        };

                        // Cache all events
                        cacheEventsByDate(prevEvents, month - 1, year);
                        cacheEventsByDate(currentEvents, month, year);
                        cacheEventsByDate(nextEvents, month + 1, year);

                        // Update current date and display
                        this.currentDate = moment([year, month - 1]);
                        this.updateSelectors();
                        this.generateCalendar();
                        this.updateCurrentDateDisplay();
                        this.updateHolidayInfo();

                    } catch (error) {
                        // Error handling - no logging
                    } finally {
                        // Re-enable navigation buttons after fetch
                        enableNavigationButtons();
                    }
                }

                // Update month/year selectors to match current date
                updateSelectors() {
                    const monthSelector = document.getElementById('month-selector');
                    const yearSelector = document.getElementById('year-selector');

                    if (monthSelector && yearSelector) {
                        monthSelector.value = this.currentDate.month() + 1;
                        yearSelector.value = this.currentDate.year();
                    }
                }

                // Toggle today button visibility
                toggleTodayButton(show = true) {
                    calendarConfig.showTodayButton = show;
                    const todayBtn = document.getElementById('today-btn');
                    if (todayBtn) {
                        todayBtn.style.display = show ? 'block' : 'none';
                    }
                }

                // Set today button text
                setTodayButtonText(text) {
                    calendarConfig.todayButtonText = text;
                    const todayBtn = document.getElementById('today-btn');
                    if (todayBtn) {
                        todayBtn.textContent = text;
                    }
                }

                generateCalendar() {
                    const calendarDays = document.getElementById('calendar-days');
                    calendarDays.innerHTML = '';

                    const startOfMonth = this.currentDate.clone().startOf('month');
                    const endOfMonth = this.currentDate.clone().endOf('month');
                    const startOfCalendar = startOfMonth.clone().startOf('week');
                    const endOfCalendar = endOfMonth.clone().endOf('week');

                    const current = startOfCalendar.clone();

                    while (current.isSameOrBefore(endOfCalendar, 'day')) {
                        const dayElement = document.createElement('div');
                        dayElement.className = 'lunar-calendar-day';
                        dayElement.dataset.day = current.date();

                        if (!current.isSame(this.currentDate, 'month')) {
                            dayElement.classList.add('other-month');
                        }

                        if (current.isSame(this.selectedDate, 'day')) {
                            dayElement.classList.add('selected');
                        }

                        if (current.isSame(moment(), 'day')) {
                            dayElement.classList.add('today');
                        }

                        const dayNumber = document.createElement('div');
                        dayNumber.className = 'lunar-day-number';
                        dayNumber.textContent = current.date();
                        dayElement.appendChild(dayNumber);

                        const lunarDay = document.createElement('div');
                        lunarDay.className = 'lunar-lunar-day';
                        const lunarDate = this.getLunarDate(current);
                        lunarDay.textContent = lunarDate.day;
                        dayElement.appendChild(lunarDay);

                        // Add events for this day
                        const dayEvent = document.createElement('div');
                        dayEvent.className = 'lunar-day-event';

                        // Get events for current day
                        const currentEvents = calendarEvents.current?.events || [];
                        const dayEvents = currentEvents.filter(event => event.day === current.date());

                        if (dayEvents.length > 0) {
                            // Check if mobile (screen width <= 680px)
                            const isMobile = window.innerWidth <= 680;

                            // Show max 3 events for both mobile and desktop
                            const eventsToShow = dayEvents.slice(0, 3);

                            if (isMobile) {
                                // Mobile: show colored dots
                                dayEvent.classList.add('mobile-event');
                                dayEvent.innerHTML = '';

                                eventsToShow.forEach(event => {
                                    const dot = document.createElement('span');
                                    dot.className = `event-dot type-${event.type || 1}`;
                                    dot.title = event.title + ': ' + event.description;
                                    dayEvent.appendChild(dot);
                                });
                            } else {
                                // Desktop: show event text
                                dayEvent.classList.add('has-event');
                                dayEvent.innerHTML = '';

                                eventsToShow.forEach((event, index) => {
                                    const eventSpan = document.createElement('span');
                                    eventSpan.className = `desktop-event-item type-${event.type || 1}`;
                                    eventSpan.textContent = event.title.length > 48 ? event.title.substring(0, 48) + '...' : event.title;
                                    eventSpan.title = event.title + ': ' + event.description;

                                    if (index > 0) {
                                        eventSpan.style.marginTop = '2px';
                                        eventSpan.style.fontSize = '0.6rem';
                                    }

                                    dayEvent.appendChild(eventSpan);
                                });
                            }

                            // Add tooltip with full description
                            dayEvent.title = dayEvents.map(e => e.title + ': ' + e.description).join('\n');
                        } else {
                            dayEvent.textContent = '';
                        }

                        dayElement.appendChild(dayEvent);

                        calendarDays.appendChild(dayElement);
                        current.add(1, 'day');
                    }
                }

                updateCalendar() {
                    // Update month/year display
                    const monthYear = `Tháng ${this.currentDate.month() + 1} - ${this.currentDate.year()}`;
                    document.getElementById('current-month-year').textContent = monthYear;

                    // Update selectors
                    document.getElementById('month-selector').value = this.currentDate.month() + 1;
                    document.getElementById('year-selector').value = this.currentDate.year();

                    // Regenerate calendar
                    this.generateCalendar();
                }
            }

            // Initialize the calendar when the page loads
            document.addEventListener('DOMContentLoaded', () => {
                const calendar = new LunarCalendar();

                // Store calendar instance globally for configuration updates
                window.lunarCalendarInstance = calendar;

                // Handle window resize to update mobile/desktop event display
                let resizeTimeout;
                window.addEventListener('resize', () => {
                    clearTimeout(resizeTimeout);
                    resizeTimeout = setTimeout(() => {
                        calendar.generateCalendar();
                    }, 250);
                });
            });
        </script>
        <?php
        return ob_get_clean();
    }
}
