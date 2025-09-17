<?php

class Jankx_Lunar_Canlendar_Loader
{
    public function __construct()
    {
        $this->load();
    }

    public function load()
    {
        $autoloader = implode(DIRECTORY_SEPARATOR, [__DIR__, 'vendor', 'autoload.php']);
        require_once $autoloader;
    }
}

$loader = new Jankx_Lunar_Canlendar_Loader();
