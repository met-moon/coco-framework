<?php

/**
 * Created by PhpStorm.
 * User: ttt
 * Date: 2015/11/21
 * Time: 20:17
 */
namespace coco\base;

class Controller
{
    public function __construct(){
        if(method_exists($this,'init')){
            $this->init();
        }
    }
    function init(){}
}