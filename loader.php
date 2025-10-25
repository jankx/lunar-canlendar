<?php

use Jankx\Gutenberg\GutenbergRepository;
use Jankx\LunarCanlendar\LunarCanlendarBlock;
use Jankx\LunarCanlendar\EventDetailBlock;
use Jankx\LunarCanlendar\EventsIntegration;

class Jankx_Lunar_Canlendar_Loader
{
    protected $eventsIntegration;

    public function __construct()
    {
        $this->load();
        $this->loadTextDomain();
    }

    public function load()
    {
        $autoloader = implode(DIRECTORY_SEPARATOR, [__DIR__, 'vendor', 'autoload.php']);
        if (file_exists($autoloader)) {
            require_once $autoloader;
        }
    }

    /**
     * Load text domain for translations
     */
    public function loadTextDomain()
    {
        $locale = apply_filters('plugin_locale', get_locale(), 'lunar-calendar');
        $mofile = sprintf('%1$s-%2$s.mo', 'lunar-calendar', $locale);
        $mofile_local = implode(DIRECTORY_SEPARATOR, [__DIR__, 'languages', $mofile]);
        $mofile_global = implode(DIRECTORY_SEPARATOR, [WP_LANG_DIR, 'plugins', $mofile]);

        if (file_exists($mofile_global)) {
            load_textdomain('lunar-calendar', $mofile_global);
        } elseif (file_exists($mofile_local)) {
            load_textdomain('lunar-calendar', $mofile_local);
        } else {
            load_plugin_textdomain(
                'lunar-calendar',
                false,
                dirname(plugin_basename(__FILE__)) . '/languages/'
            );
        }
    }

    public function registerBlocks() {
        $loader = $this;

        add_action('jankx/gutenberg/register-blocks', function(GutenbergRepository $repository) use ($loader) {
            // Always register Lunar Calendar block
            $repository->registerBlock(
                LunarCanlendarBlock::class,
                implode(DIRECTORY_SEPARATOR, [dirname(__FILE__), 'build', 'lunar-calendar'])
            );
        }, 10, 1);

        // Đăng ký AJAX handlers ngay từ đầu
        LunarCanlendarBlock::register_ajax_handler();
    }
}

$loader = new Jankx_Lunar_Canlendar_Loader();
$loader->registerBlocks();
