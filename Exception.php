<?php

/**
 * CoCo Exception
 * User: ttt
 * Date: 2016/3/6
 * Time: 20:38
 */
namespace coco;
class Exception extends \Exception
{
    public function __construct($title, $description, $code = 0, Exception $previous = null)
    {
        $message = '<h1 style="color: red;">' . $title . '</h1><h2>' . $description . '</h2>';
        parent::__construct($message, $code, $previous);
    }
}