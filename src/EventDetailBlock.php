<?php
namespace Jankx\LunarCanlendar;

use Jankx\Gutenberg\Block;

/**
 * Event Detail Block
 * Hiển thị thông tin chi tiết sự kiện từ Events Manager
 */
class EventDetailBlock extends Block
{
    protected $blockId = 'jankx/event-details';

    /**
     * Get event object from global or post
     */
    protected function getEvent()
    {
        global $post;

        // Check if EM_Event class exists (Events Manager is active)
        if (!class_exists('EM_Event')) {
            return null;
        }

        // Try to get event from global
        global $EM_Event;
        if (!empty($EM_Event) && is_a($EM_Event, 'EM_Event')) {
            return $EM_Event;
        }

        // Try to get event from current post
        if ($post && $post->post_type === 'event') {
            return em_get_event($post->ID, 'post_id');
        }

        return null;
    }

    /**
     * Format date/time for display
     */
    protected function formatDateTime($event, $show_date = true, $show_time = true)
    {
        if (!$event) {
            return '';
        }

        $output = [];

        if ($show_date) {
            $start_date = $event->event_start_date;
            $end_date = $event->event_end_date;

            // Format date
            $formatted_start = date_i18n('d/m/Y', strtotime($start_date));

            if ($start_date !== $end_date) {
                $formatted_end = date_i18n('d/m/Y', strtotime($end_date));
                $output[] = sprintf('%s - %s', $formatted_start, $formatted_end);
            } else {
                $output[] = $formatted_start;
            }
        }

        if ($show_time) {
            $start_time = $event->event_start_time;
            $end_time = $event->event_end_time;

            if ($start_time && $start_time !== '00:00:00') {
                $formatted_start_time = date_i18n('H:i', strtotime($start_time));

                if ($end_time && $end_time !== '00:00:00' && $start_time !== $end_time) {
                    $formatted_end_time = date_i18n('H:i', strtotime($end_time));
                    $output[] = sprintf('%s - %s', $formatted_start_time, $formatted_end_time);
                } else {
                    $output[] = $formatted_start_time;
                }
            }
        }

        return implode(' | ', $output);
    }

    /**
     * Get location info
     */
    protected function getLocationInfo($event)
    {
        if (!$event || !$event->get_location()) {
            return null;
        }

        $location = $event->get_location();

        return [
            'name' => $location->location_name,
            'address' => $location->location_address,
            'town' => $location->location_town,
            'url' => $location->get_permalink(),
        ];
    }

    /**
     * Get organizer/owner info
     */
    protected function getOrganizerInfo($event)
    {
        if (!$event) {
            return null;
        }

        // Get event owner
        $owner_id = $event->event_owner;
        if (!$owner_id) {
            return null;
        }

        $user = get_userdata($owner_id);
        if (!$user) {
            return null;
        }

        return [
            'name' => $user->display_name,
            'url' => get_author_posts_url($owner_id),
        ];
    }

    /**
     * Get event categories
     */
    protected function getEventCategories($event)
    {
        if (!$event) {
            return [];
        }

        $categories = get_the_terms($event->post_id, 'event-categories');

        if (empty($categories) || is_wp_error($categories)) {
            return [];
        }

        return array_map(function($term) {
            return [
                'name' => $term->name,
                'url' => get_term_link($term),
                'slug' => $term->slug,
            ];
        }, $categories);
    }

    /**
     * Get booking information
     */
    protected function getBookingInfo($event)
    {
        if (!$event) {
            return null;
        }

        $info = [];

        // Check if event has bookings enabled
        if (!$event->event_rsvp) {
            return null;
        }

        // Total spaces
        $event_spaces = $event->get_spaces();
        if ($event_spaces > 0) {
            $available_spaces = $event->get_bookings()->get_available_spaces();
            $info['total_spaces'] = $event_spaces;
            $info['available_spaces'] = $available_spaces;
            $info['booked_spaces'] = $event_spaces - $available_spaces;
        }

        // Maximum spaces per booking
        if (!empty($event->event_rsvp_spaces)) {
            $info['max_per_booking'] = $event->event_rsvp_spaces;
        }

        // Booking cut-off date
        if (!empty($event->event_rsvp_date) && $event->event_rsvp_date !== '0000-00-00 00:00:00') {
            $info['booking_deadline'] = date_i18n('d/m/Y H:i', strtotime($event->event_rsvp_date));
            $info['is_deadline_passed'] = strtotime($event->event_rsvp_date) < current_time('timestamp');
        }

        // Check if bookings are still open
        $info['bookings_open'] = $event->get_bookings()->is_open();

        // Get booking URL/button
        $info['booking_url'] = $event->get_bookings_url();

        return $info;
    }

    /**
     * Render block output
     */
    public function render($attributes = [], $content = '', $block = null)
    {
        // Get event object
        $event = $this->getEvent();

        // Extract attributes with defaults
        $show_date = isset($attributes['showDate']) ? $attributes['showDate'] : true;
        $show_time = isset($attributes['showTime']) ? $attributes['showTime'] : true;
        $show_location = isset($attributes['showLocation']) ? $attributes['showLocation'] : true;
        $show_organizer = isset($attributes['showOrganizer']) ? $attributes['showOrganizer'] : true;
        $show_categories = isset($attributes['showCategories']) ? $attributes['showCategories'] : true;
        $show_booking_info = isset($attributes['showBookingInfo']) ? $attributes['showBookingInfo'] : true;
        $show_booking_button = isset($attributes['showBookingButton']) ? $attributes['showBookingButton'] : true;
        $booking_button_text = isset($attributes['bookingButtonText']) ? $attributes['bookingButtonText'] : __('Đăng ký tham gia', 'lunar-calendar');

        // If no event found, show message
        if (!$event) {
            return sprintf(
                '<div class="jankx-event-details"><div class="event-no-data">%s</div></div>',
                esc_html__('Không tìm thấy thông tin sự kiện', 'lunar-calendar')
            );
        }

        // Build output
        ob_start();
        ?>
        <div class="jankx-event-details">
            <div class="event-details-wrapper">
                <?php if ($show_date || $show_time):
                    $datetime = $this->formatDateTime($event, $show_date, $show_time);
                    if ($datetime):
                ?>
                <div class="event-detail-item">
                    <span class="detail-icon">📅</span>
                    <div class="detail-content">
                        <strong><?php esc_html_e('Thời gian:', 'lunar-calendar'); ?></strong>
                        <p><?php echo esc_html($datetime); ?></p>
                    </div>
                </div>
                <?php endif; endif; ?>

                <?php if ($show_location):
                    $location = $this->getLocationInfo($event);
                    if ($location):
                ?>
                <div class="event-detail-item">
                    <span class="detail-icon">📍</span>
                    <div class="detail-content">
                        <strong><?php esc_html_e('Địa điểm:', 'lunar-calendar'); ?></strong>
                        <p>
                            <?php if ($location['url']): ?>
                                <a href="<?php echo esc_url($location['url']); ?>" target="_blank">
                                    <?php echo esc_html($location['name']); ?>
                                </a>
                            <?php else: ?>
                                <?php echo esc_html($location['name']); ?>
                            <?php endif; ?>
                            <?php if ($location['address'] || $location['town']): ?>
                                <br>
                                <small>
                                    <?php
                                    $address_parts = array_filter([$location['address'], $location['town']]);
                                    echo esc_html(implode(', ', $address_parts));
                                    ?>
                                </small>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
                <?php endif; endif; ?>

                <?php if ($show_organizer):
                    $organizer = $this->getOrganizerInfo($event);
                    if ($organizer):
                ?>
                <div class="event-detail-item">
                    <span class="detail-icon">👥</span>
                    <div class="detail-content">
                        <strong><?php esc_html_e('Tổ chức:', 'lunar-calendar'); ?></strong>
                        <p>
                            <a href="<?php echo esc_url($organizer['url']); ?>">
                                <?php echo esc_html($organizer['name']); ?>
                            </a>
                        </p>
                    </div>
                </div>
                <?php endif; endif; ?>

                <?php if ($show_categories):
                    $categories = $this->getEventCategories($event);
                    if (!empty($categories)):
                ?>
                <div class="event-detail-item">
                    <span class="detail-icon">🏷️</span>
                    <div class="detail-content">
                        <strong><?php esc_html_e('Danh mục:', 'lunar-calendar'); ?></strong>
                        <div class="event-categories">
                            <?php foreach ($categories as $category): ?>
                                <a href="<?php echo esc_url($category['url']); ?>"
                                   class="event-category">
                                    <?php echo esc_html($category['name']); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; endif; ?>

                <?php if ($show_booking_info):
                    $booking_info = $this->getBookingInfo($event);
                    if ($booking_info):
                ?>
                <div class="event-detail-item event-booking-info">
                    <span class="detail-icon">🎫</span>
                    <div class="detail-content">
                        <strong><?php esc_html_e('Thông tin đăng ký:', 'lunar-calendar'); ?></strong>

                        <?php if (isset($booking_info['total_spaces'])): ?>
                        <p class="booking-spaces">
                            <span class="booking-stat">
                                <strong><?php esc_html_e('Còn trống:', 'lunar-calendar'); ?></strong>
                                <span class="stat-value <?php echo $booking_info['available_spaces'] <= 5 ? 'low-availability' : ''; ?>">
                                    <?php echo esc_html($booking_info['available_spaces']); ?>/<?php echo esc_html($booking_info['total_spaces']); ?>
                                </span>
                            </span>
                        </p>
                        <?php endif; ?>

                        <?php if (isset($booking_info['max_per_booking'])): ?>
                        <p class="booking-limit">
                            <span class="booking-stat">
                                <strong><?php esc_html_e('Giới hạn/người:', 'lunar-calendar'); ?></strong>
                                <span class="stat-value"><?php echo esc_html($booking_info['max_per_booking']); ?> chỗ</span>
                            </span>
                        </p>
                        <?php endif; ?>

                        <?php if (isset($booking_info['booking_deadline'])): ?>
                        <p class="booking-deadline <?php echo $booking_info['is_deadline_passed'] ? 'deadline-passed' : ''; ?>">
                            <span class="booking-stat">
                                <strong><?php esc_html_e('Hạn đăng ký:', 'lunar-calendar'); ?></strong>
                                <span class="stat-value">
                                    <?php echo esc_html($booking_info['booking_deadline']); ?>
                                    <?php if ($booking_info['is_deadline_passed']): ?>
                                        <span class="deadline-status">(<?php esc_html_e('Đã hết hạn', 'lunar-calendar'); ?>)</span>
                                    <?php endif; ?>
                                </span>
                            </span>
                        </p>
                        <?php endif; ?>

                        <?php if (!$booking_info['bookings_open']): ?>
                        <p class="booking-closed-notice">
                            <?php esc_html_e('Đăng ký đã đóng', 'lunar-calendar'); ?>
                        </p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; endif; ?>

                <?php if ($show_booking_button):
                    $booking_info = $this->getBookingInfo($event);
                    if ($booking_info && $booking_info['bookings_open'] && !empty($booking_info['booking_url'])):
                ?>
                <div class="event-booking-button-wrapper">
                    <a href="<?php echo esc_url($booking_info['booking_url']); ?>"
                       class="event-booking-button">
                        <?php echo esc_html($booking_button_text); ?>
                    </a>
                </div>
                <?php endif; endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}


