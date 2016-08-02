<?php
/**
 * Created by PhpStorm.
 * User: ttt
 * Date: 2016/8/2
 * Time: 14:12
 */

require_once 'CoCo.php';
require_once 'ClassLoader.php';

CoCo::init();

ClassLoader::addPrefix('coco\\', __DIR__);
spl_autoload_register('ClassLoader::autoload', true, true);