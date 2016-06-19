<?php

// default charset is utf-8
header('Content-type:text/html;charset=utf-8');

// set timezone
date_default_timezone_set('Asia/Shanghai');

// some functions
include_once 'functions.php';

//default debug model false
defined('COCO_DEBUG') or define('COCO_DEBUG', false);

// default environment is production
defined('COCO_ENV') or define('COCO_ENV', 'pro');

// class autoload
require_once 'ClassLoader.php';
ClassLoader::addPrefix('coco\\', __DIR__);

spl_autoload_register('ClassLoader::autoload', true, true);

class CoCo
{
    /**
     * @var \coco\web\Application
     */
    public static $app;

    public static function getVersion()
    {
        return 'v0.3-dev';
    }
}

