<?php
namespace coco;

/**
 * Class CoCo
 * @package coco
 */
class CoCo
{
    /**
     * @var \coco\web\Application
     */
    public static $app;

    public static function getVersion()
    {
        return 'v0.4';
    }

    public static function init(){
        // some functions
        include_once 'functions.php';

        //default debug model false
        defined('COCO_DEBUG') or define('COCO_DEBUG', false);

        // default environment is production
        //  dev | test | prod
        /**
         * application environment
         * default environment is production(prod)
         * dev | test | prod
         */
        defined('COCO_ENV') or define('COCO_ENV', 'prod');
    }
}

