<?php
namespace Jankx\LunarCanlendar;

/**
 * Events Manager Integration
 * Handles Gutenberg integration with Events Manager plugin
 */
class EventsIntegration
{
    /**
     * Initialize integration
     */
    public function init()
    {
        // Force Gutenberg editor for Events Manager
        add_filter('use_block_editor_for_post_type', [$this, 'enableBlockEditorForEvent'], 10, 2);

        // Force block template loading - use template_include as final hook
        add_filter('template_include', [$this, 'forceBlockTemplateLoad'], 999);

        // Disable Events Manager default formatting for event content
        add_filter('option_dbem_cp_events_formats', [$this, 'disableEventManagerFormatting'], 10, 1);
        add_filter('em_event_output_single', [$this, 'useGutenbergContent'], 10, 3);
    }

    /**
     * Enable Gutenberg block editor for event post type
     *
     * @param bool $use_block_editor Whether to use block editor
     * @param string $post_type Post type
     * @return bool
     */
    public function enableBlockEditorForEvent($use_block_editor, $post_type)
    {
        if ($post_type === 'event') {
            return true;
        }
        return $use_block_editor;
    }

    /**
     * Force block template loading for event pages
     * This hook runs last (priority 999) to override any other template selection
     *
     * @param string $template Template path
     * @return string Modified template path
     */
    public function forceBlockTemplateLoad($template)
    {
        if (!function_exists('wp_is_block_theme') || !wp_is_block_theme()) {
            return $template;
        }

        // Check for single event
        if (is_singular('event')) {
            $block_template = locate_block_template($template, 'single-event', ['single-event.html']);

            if (!empty($block_template)) {
                return $block_template;
            }
        }

        // Check for event archives (post type archive, taxonomies, Events Manager custom URLs)
        $is_event_archive = false;

        if (is_post_type_archive('event')) {
            $is_event_archive = true;
        }

        // Check if current taxonomy is related to events
        if (is_tax()) {
            $queried_object = get_queried_object();
            if ($queried_object && isset($queried_object->taxonomy)) {
                // Check if taxonomy name starts with 'event-' or 'event_'
                if (strpos($queried_object->taxonomy, 'event-') === 0 ||
                    strpos($queried_object->taxonomy, 'event_') === 0) {
                    $is_event_archive = true;
                }
            }
        }

        // Check Events Manager custom query vars (for URLs like /events/categories/le/)
        global $wp_query;
        if (isset($wp_query->query_vars['post_type']) && $wp_query->query_vars['post_type'] === 'event') {
            $is_event_archive = true;
        }

        // Check for Events Manager taxonomy constants
        if (defined('EM_TAXONOMY_CATEGORY') && is_tax(EM_TAXONOMY_CATEGORY)) {
            $is_event_archive = true;
        }
        if (defined('EM_TAXONOMY_TAG') && is_tax(EM_TAXONOMY_TAG)) {
            $is_event_archive = true;
        }

        // Check if URL contains /events/ path (last resort)
        if (!$is_event_archive && isset($_SERVER['REQUEST_URI'])) {
            $request_uri = $_SERVER['REQUEST_URI'];
            if (strpos($request_uri, '/events/') !== false) {
                $is_event_archive = true;
            }
        }

        if ($is_event_archive) {
            $block_template = locate_block_template($template, 'archive-event', ['archive-event.html']);

            if (!empty($block_template)) {
                return $block_template;
            }
        }

        // Debug: Log when on events URL but template not found (only for logged in admins)
        if (current_user_can('manage_options') && isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/events/') !== false) {
            error_log('EventsIntegration Debug: is_event_archive=' . ($is_event_archive ? 'true' : 'false'));
            error_log('EventsIntegration Debug: template=' . $template);
            error_log('EventsIntegration Debug: is_tax=' . (is_tax() ? 'true' : 'false'));
            error_log('EventsIntegration Debug: is_post_type_archive(event)=' . (is_post_type_archive('event') ? 'true' : 'false'));
            if (is_tax()) {
                $obj = get_queried_object();
                error_log('EventsIntegration Debug: taxonomy=' . (isset($obj->taxonomy) ? $obj->taxonomy : 'none'));
            }
        }

        return $template;
    }

    /**
     * Disable Events Manager default formatting
     *
     * @param mixed $value Option value
     * @return string Empty format to disable EM formatting
     */
    public function disableEventManagerFormatting($value)
    {
        // Return empty string to disable Events Manager's default event formatting
        // This allows Gutenberg to handle the content rendering
        return '';
    }

    /**
     * Use Gutenberg content for single events
     *
     * @param string $output Event output
     * @param object $event Event object
     * @param string $format Format string
     * @return string Modified output
     */
    public function useGutenbergContent($output, $event, $format)
    {
        // If post has blocks, return the post content (Gutenberg blocks)
        if (has_blocks($event->post_content)) {
            return do_blocks($event->post_content);
        }

        // Otherwise return the Events Manager default output
        return $output;
    }
}

