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
include_once __DIR__ . '/ClassLoader.php';

ClassLoader::$fileExt = '.php';
ClassLoader::$prefixMap = [
    'coco\\' => 'coco/'
];

spl_autoload_register('ClassLoader::autoload', true, true);

class CoCo
{
    public static $app;

    public static function getVersion()
    {
        return 'v0.2-dev';
    }
}

