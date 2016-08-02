<?php
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

    public static function init(){
        // some functions
        include_once 'functions.php';

        //default debug model false
        defined('COCO_DEBUG') or define('COCO_DEBUG', false);

        // default environment is production
        defined('COCO_ENV') or define('COCO_ENV', 'pro');
    }
}

