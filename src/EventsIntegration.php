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
        
        // Force block template for Events Manager
        add_filter('single_template', [$this, 'forceEventBlockTemplate'], 20, 3);
        
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
     * Force block template for event post type
     * Override Events Manager's template selection with priority 20
     *
     * @param string $template Template path
     * @param string $type Template type
     * @param array $templates Template hierarchy
     * @return string Modified template path
     */
    public function forceEventBlockTemplate($template, $type = '', $templates = [])
    {
        if (is_singular('event') && function_exists('wp_is_block_theme') && wp_is_block_theme()) {
            // Try to locate single-event.html block template
            $block_template = locate_block_template($template, 'single-event', ['single-event.html']);
            
            if (!empty($block_template)) {
                return $block_template;
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

