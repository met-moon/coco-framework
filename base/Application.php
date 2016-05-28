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
    public $config;
    public $appPath;

    public $timeZone = 'UTC';
    public $charset = 'UTF-8';

    public $module;
    public $controller;
    public $action;

    public $defaultModule = 'index';
    public $defaultController = 'index';
    public $defaultAction = 'index';

    public $controllerNamespace = 'app\\controllers';

    public $currentController;

    public $routeType;

}
