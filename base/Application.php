<?php

namespace coco\base;
/**
 * Base Application
 * User: ttt
 * Date: 2015/11/21
 * Time: 22:10
 */
class Application
{
    public function __get($name)
    {
        var_dump($name);
    }
}