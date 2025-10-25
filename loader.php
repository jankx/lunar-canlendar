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
    }

    public function load()
    {
        $autoloader = implode(DIRECTORY_SEPARATOR, [__DIR__, 'vendor', 'autoload.php']);
        if (file_exists($autoloader)) {
            require_once $autoloader;
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
