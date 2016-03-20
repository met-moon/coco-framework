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
            $msg = '<pre>';
            $msg .= $e->getMessage();
            foreach ($e->getTrace() as $f) {
                $msg .= $f['file'] . ' in line ' . $f['line'] . ' -> ' . $f['class'] . '::' . $f['function'].'()' . PHP_EOL;
            }
            $msg .= '<hr>' . date('Y-m-d H:i:s') . '  CoCo Framework ' . CoCo::getVersion() . ' </pre>';
        }
        //echo '<script>console.log("error-info:'.$msg.'\n")</script>';
        echo $msg;
    }
}