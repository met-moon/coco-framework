<?php

namespace coco\base;

/**
 * Base Application
 * @property \coco\db\Db $db The database connection. This property is read-only.
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

    public function __get($name)
    {
        if(isset($this->config['components'][$name])){
            $className = $this->config['components'][$name]['class'];
            $params = $this->config['components'][$name];
            unset($params['class']);
            return $this->$name = new $className($params);
        }
    }
}
