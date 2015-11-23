<?php
/**
 * Created by PhpStorm.
 * User: ttt
 * Date: 2015/11/23
 * Time: 23:02
 */

namespace coco\web;

class View extends \coco\base\View
{
    public $title = '';

    public $js;

    public $css;

    public $content = '';

    public $params = [];

    public $layout = 'main';

    public $defaultExtension = 'php';

    public function render(){

    }
}