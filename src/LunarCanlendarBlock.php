<?php
namespace Jankx\LunarCanlendar;

use Jankx\Gutenberg\Block;

class LunarCanlendarBlock extends Block {
    protected $blockId = 'jankx/lunar-calendar';

    public function init() {
        add_action('wp_enqueue_scripts', function() {
            // Third-party deps
            wp_enqueue_script(
                'moment',
                'https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js',
                array(),
                '2.29.4',
                true
            );
            wp_enqueue_script(
                'moment-locale-vi',
                'https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/locale/vi.min.js',
                array('moment'),
                '2.29.4',
                true
            );
            wp_enqueue_script(
                'lunar-date-vi',
                'https://cdn.jsdelivr.net/npm/@nghiavuive/lunar_date_vi@2.0.1/dist/index.umd.min.js',
                array(),
                null,
                true
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
}
