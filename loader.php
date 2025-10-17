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
        $this->initIntegrations();
    }

    public function load()
    {
        $autoloader = implode(DIRECTORY_SEPARATOR, [__DIR__, 'vendor', 'autoload.php']);
        if (file_exists($autoloader)) {
            require_once $autoloader;
        }
    }

    /**
     * Check if Events Manager plugin is active
     */
    protected function isEventsManagerActive()
    {
        // Check if EM_Event class exists (Events Manager is loaded)
        if (class_exists('EM_Event')) {
            return true;
        }

        // Alternative check: plugin file exists and is active
        if (function_exists('em_get_event')) {
            return true;
        }

        return false;
    }

    /**
     * Initialize integrations with Events Manager
     */
    protected function initIntegrations()
    {
        if ($this->isEventsManagerActive()) {
            $this->eventsIntegration = new EventsIntegration();
            $this->eventsIntegration->init();
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

            // Register Event Detail block only if Events Manager is active
            if ($loader->isEventsManagerActive()) {
                $repository->registerBlock(
                    EventDetailBlock::class,
                    implode(DIRECTORY_SEPARATOR, [dirname(__FILE__), 'build', 'event-details'])
                );
            }
        }, 10, 1);

        // Đăng ký AJAX handlers ngay từ đầu
        LunarCanlendarBlock::register_ajax_handler();
    }
}

$loader = new Jankx_Lunar_Canlendar_Loader();
$loader->registerBlocks();
