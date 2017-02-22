<?php
/**
 * if not use composer's autoload
 * include this file
 * User: ttt
 * Date: 2016/8/2
 * Time: 14:12
 */

require_once 'ClassLoader.php';

\coco\ClassLoader::addPrefix('coco\\', __DIR__);
spl_autoload_register('coco\ClassLoader::autoload', true, true);