<?php

use Jankx\Gutenberg\GutenbergRepository;
use Jankx\LunarCanlendar\LunarCanlendarBlock;

class Jankx_Lunar_Canlendar_Loader
{
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
        add_action('jankx/gutenberg/register-blocks', function(GutenbergRepository $repository){
            $repository->registerBlock(LunarCanlendarBlock::class, implode(DIRECTORY_SEPARATOR, [dirname(__FILE__), 'build', 'lunar-calendar']));
        });

        // Đăng ký AJAX handlers ngay từ đầu
        LunarCanlendarBlock::register_ajax_handler();
    }
}

$loader = new Jankx_Lunar_Canlendar_Loader();
$loader->registerBlocks();
