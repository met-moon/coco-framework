<?php

if (!function_exists('config')) {
    /**
     * get a config
     * @param string $key
     * @param bool $throw
     * @return mixed|null
     */
    function config($key, $throw = false)
    {
        return coco\config\Config::get($key, $throw);
    }
}



