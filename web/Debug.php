<?php
/**
 * Created by PhpStorm.
 * User: ttt
 * Date: 2016/3/6
 * Time: 23:11
 */

namespace coco\web;

use CoCo;
use coco\Exception;

class Debug
{
    public static function catchException(Exception $e){
        if(COCO_DEBUG){
            echo '<pre>';
            echo $e->getMessage();
            foreach ($e->getTrace() as $f) {
                echo $f['file'] . ' in line ' . $f['line'] . ' -> ' . $f['class'] . '::' . $f['function'].'()' . PHP_EOL;
            }
            echo '<hr>' . date('Y-m-d H:i:s') . '  CoCo Framework ' . CoCo::getVersion() . ' </pre>';
        }
    }
}