<?php

namespace coco\web;

use CoCo;
use ClassLoader;
use coco\Exception;

/**
 * Web Application
 * User: ttt
 * Date: 2015/11/21
 * Time: 20:15
 */
class Application extends \coco\base\Application
{
    public function __construct($config)
    {
        if (!is_null($config)) {
            $this->config = $config;
        }
        CoCo::$app = $this;
    }

    /**
     * Bootstrap
     * @return $this
     */
    public function bootstrap()
    {
        return $this;
    }

    /**
     * Run the Application
     */
    public function run()
    {
        try {
            $this->startSession();
            $this->initConfig();
            ClassLoader::addPrefix('app\\', $this->config['appPath']);
            $this->dispatch();
        } catch (Exception $e) {
            Debug::catchException($e);
        }
    }

    /**
     * start session
     */
    protected function startSession()
    {
        if (isset($this->config['session']['start']) && $this->config['session']['start'] === false) {
            return;
        }
        if (!empty($this->config['session']['name'])) {
            session_name($this->config['session']['name']);
        }
        session_start();
    }

    /**
     * Initialize the Application Configuration
     */
    public function initConfig()
    {
        if (!empty($this->config['appPath']) && is_dir($this->config['appPath'])) {
            $this->appPath = realpath($this->config['appPath']);
        } else {
            header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error');
            throw new Exception('Configuration Error', 'Configuration "appPath" is required!');
        }

        if (!empty($this->config['defaultModule'])) {
            $this->defaultModule = $this->config['defaultModule'];
        }

        if (!empty($this->config['defaultController'])) {
            $this->defaultController = $this->config['defaultController'];
        }

        if (!empty($this->config['defaultAction'])) {
            $this->defaultAction = $this->config['defaultAction'];
        }

        if (!empty($this->config['controllerNamespace'])) {
            $this->controllerNamespace = $this->config['controllerNamespace'];
        }

        if (!empty($this->config['timeZone'])) {
            $this->timeZone = $this->config['timeZone'];
        }

        if (!empty($this->config['charset'])) {
            $this->charset = $this->config['charset'];
        }

        if (isset($this->config['route']['type'])) {
            $this->routeType = $this->config['route']['type'];
        }
    }

    /**
     * route
     */
    public function parseRequest()
    {
        // parse url
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $scriptPathInfo = pathinfo($_SERVER['SCRIPT_NAME']);
        $indexFile = '/' . $scriptPathInfo['basename'];
        if (strpos($uri, $indexFile) !== false) {    //has index.php
            $uri = substr($uri, strlen($_SERVER['SCRIPT_NAME']));
        } else {  //not has index.php
            $uri = substr($uri, strlen($scriptPathInfo['dirname']));
        }

        if (!empty($this->config['route']['urlSuffix'])) {
            // remove the url suffix
            if (strpos($uri, $this->config['route']['urlSuffix']) !== false) {
                $uri = strstr($uri, $this->config['route']['urlSuffix'], true);
            }
        }

        $uri = trim($uri, '/');

        // request /defaultModule/defaultController/defaultAction
        if (empty($uri)) {
            $this->module = $this->defaultModule;
            $this->controller = $this->defaultController;
            $this->action = $this->defaultAction;
        } else {
            $uriPathArr = explode('/', $uri);
            if (count($uriPathArr) == 1) {    // request /defaultModule/controller/defaultAction or /module/defaultController/defaultAction
                $this->module = $this->defaultModule;
                $this->controller = $uriPathArr[0];
                $this->action = $this->defaultAction;

                $className = $this->controllerNamespace . '\\' . ucfirst($this->controller) . 'Controller';
                if (!class_exists($className)) {
                    $this->module = $uriPathArr[0];
                    $this->controller = $this->defaultController;
                    $this->action = $this->defaultAction;
                }
            } else if (count($uriPathArr) == 2) {    // request /defaultModule/controller/action or /module/controller/defaultAction
                $this->module = $this->defaultModule;
                $this->controller = $uriPathArr[0];
                $this->action = $uriPathArr[1];

                $className = $this->controllerNamespace . '\\' . ucfirst($this->controller) . 'Controller';
                if (!class_exists($className)) {
                    $this->module = $uriPathArr[0];
                    $this->controller = $uriPathArr[1];
                    $this->action = $this->defaultAction;
                }
            } else if (count($uriPathArr) == 3) {  // request /module/controller/action
                $this->module = $uriPathArr[0];
                $this->controller = $uriPathArr[1];
                $this->action = $uriPathArr[2];
            } else {  // request /module/controller/action/params0key/params0value/...
                $this->module = $uriPathArr[0];
                $this->controller = $uriPathArr[1];
                $this->action = $uriPathArr[2];

                for ($i = 3; $i < count($uriPathArr); $i += 2) {
                    if (isset($uriPathArr[($i + 1)])) {
                        $_GET[$uriPathArr[$i]] = $uriPathArr[($i + 1)]; //TODO set request Query
                    } else {
                        $_GET[$uriPathArr[$i]] = '';
                    }
                }
            }
        }
    }

    /**
     * dispatch
     * @throws Exception
     */
    public function dispatch()
    {
        if ($this->routeType == 1) {
            //require_once $this->config['route']['includeRulesFile'];
            Route::haltOnMatch();
            Route::error(function ($route) {
                header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
                throw new Exception('Page Not Found', "This route：\"{$_SERVER['REQUEST_METHOD']}: $route\" No matching items!");
            });
            Route::dispatch();
            $this->end();
        }

        $this->parseRequest();

        // Joining together the controller class name
        if ($this->module == $this->defaultModule) {
            $className = $this->controllerNamespace . '\\' . $this->controller . 'Controller';
        } else {  // Joining together the controller class name
            if (!empty($this->config['modules'][$this->module]['controllerNamespace'])) {
                $className = $this->config['modules'][$this->module]['controllerNamespace'] . '\\' . ucfirst($this->controller) . 'Controller';
            } else {
                $className = 'app\\modules\\' . $this->module . '\\controllers\\' . ucfirst($this->controller) . 'Controller';
            }
        }

        // check controller class exists
        if (!class_exists($className)) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
            throw new Exception('Page Not Found', 'Controller ' . $className . ' not exists!');
        }

        // Instantiate the controller
        $controllerObj = new $className();

        if (!(!is_null($controllerObj) && $controllerObj instanceof Controller)) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
            throw new Exception('Page Not Found', 'Controller ' . $className . ' has error!');
        }

        $action = 'action' . ucfirst($this->action);

        // check action method exists
        if (!method_exists($controllerObj, $action)) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
            throw new Exception('Page Not Found', 'Action ' . get_class($controllerObj) . '::' . $action . ' not exists!');
        }

        $this->currentController = $controllerObj;

        // action
        $controllerObj->$action();
    }

    /**
     * create a relative Url
     * @param string $path
     * @param array $params
     * @return string
     */
    public function createUrl($path, $params = [])
    {
        if (empty($path)) {
            return '';
        }

        $basePath = pathinfo($_SERVER['SCRIPT_NAME']);
        $basePath = rtrim($basePath['dirname'], '/');
        $url = rtrim($path, '/');
        $url = $basePath . $url;
        $url = strtolower($url);
        if (!empty($params)) {
            foreach ($params as $k => $v) {
                $url .= "/$k/$v";
            }
        }
        // url suffix
        if (!empty($this->config['route']['urlSuffix'])) {
            $url .= $this->config['route']['urlSuffix'];
        }

        return $url;
    }

    public function basePath()
    {
        $path = pathinfo($_SERVER['SCRIPT_NAME']);
        return rtrim($path['dirname'], '/') . '/';
    }

    public function end()
    {
        exit(0);
    }
}
