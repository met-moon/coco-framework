<?php

// default charset is utf-8
header('Content-type:text/html;charset=utf-8');

// set timezone
date_default_timezone_set('Asia/Shanghai');

// session
session_start();

// CoCo version
const COCO_VERSION = 'v0.2';

//default debug model false
defined('COCO_DEBUG') or define('COCO_DEBUG', false);

//default environment is production
defined('COCO_ENV') or define('COCO_ENV', 'pro');

// class autoload
include __DIR__.'/ClassLoader.php';
ClassLoader::$fileExt = '.php';
ClassLoader::$basePath = dirname(__DIR__);
ClassLoader::$prefixMap = [
    'coco\\'=>'coco/'
];
spl_autoload_register('ClassLoader::autoload', true, true);

class CoCo{

    public static $app;
    public static function getVersion()
    {
        return '0.2';
    }

}

