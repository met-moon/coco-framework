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
    public $config;
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
    public function bootstrap(){
        return $this;
    }

    /**
     * Run the Application
     */
    public function run()
    {
        try{
            $this->startSession();
            ClassLoader::addPrefix('app\\', CoCo::$app->config['appPath']);
            $this->checkConfig();
            $this->route();
            $this->dispatch();
        }catch (Exception $e){
            Debug::catchException($e);
        }
    }

    /**
     * start session
     */
    protected function startSession(){
        if(isset(CoCo::$app->config['session']['start']) && CoCo::$app->config['session']['start'] === false){
            return;
        }
        if(!empty(CoCo::$app->config['session']['name'])){
            session_name(CoCo::$app->config['session']['name']);
        }
        session_start();
    }

    /**
     * Check the Application Configuration
     */
    public function checkConfig(){
        if(!empty(CoCo::$app->config['appPath']) && is_dir(CoCo::$app->config['appPath'])){
            CoCo::$app->appPath = CoCo::$app->config['appPath'];
            //ClassLoader::$basePath = dirname(CoCo::$app->config['appPath']);
        }else{
            header('HTTP/1.1 500 Internal Server Error');
            throw new Exception('Configuration Error','Configuration "appPath" is required!');
        }
    }

    /**
     * route
     */
    public function route(){
        $defaultModule = !empty(CoCo::$app->config['defaultModule']) ? CoCo::$app->config['defaultModule'] : 'index';
        $defaultController = !empty(CoCo::$app->config['defaultController']) ? CoCo::$app->config['defaultController'] : 'index';
        $defaultAction = !empty(CoCo::$app->config['defaultAction']) ? CoCo::$app->config['defaultAction'] : 'index';

        CoCo::$app->defaultModule = $defaultModule;
        CoCo::$app->defaultController = ucfirst($defaultController);
        CoCo::$app->defaultAction = ucfirst($defaultAction);

        // parse url
        $uriArr = parse_url($_SERVER['REQUEST_URI']);
        $scriptPathInfo = pathinfo($_SERVER['SCRIPT_NAME']);
        $indexFile = '/'.$scriptPathInfo['basename'];
        if(strpos($uriArr['path'], $indexFile) !== false){    //has index.php
            $uriArr['path'] = substr($uriArr['path'], strlen($_SERVER['SCRIPT_NAME']));
        }else{  //not has index.php
            $uriArr['path'] = substr($uriArr['path'], strlen($scriptPathInfo['dirname']));
        }

        if (!empty(CoCo::$app->config['url']['suffix'])) {
            // remove the url suffix
            if(strpos($uriArr['path'], CoCo::$app->config['url']['suffix']) !== false){
                $uriArr['path'] = strstr($uriArr['path'], CoCo::$app->config['url']['suffix'], true);
            }
        }
        $uriArr['path'] = ltrim($uriArr['path'], '/');

        // request /defaultModule/defaultController/defaultAction
        if(empty($uriArr['path'])){
            CoCo::$app->module = CoCo::$app->defaultModule;
            CoCo::$app->controller = CoCo::$app->defaultController;
            CoCo::$app->action = CoCo::$app->defaultAction;
        }else{
            $uriPathArr = explode('/', $uriArr['path']);
            if(count($uriPathArr) == 1){    // request /defaultModule/controller/defaultAction or /module/defaultController/defaultAction
                CoCo::$app->module = CoCo::$app->defaultModule;
                CoCo::$app->controller = ucfirst($uriPathArr[0]);
                CoCo::$app->action = CoCo::$app->defaultAction;

                $className = 'app\\' . 'controllers\\' . CoCo::$app->controller . 'Controller';
                if(!class_exists($className)){
                    CoCo::$app->module = $uriPathArr[0];
                    CoCo::$app->controller = CoCo::$app->defaultController;
                    CoCo::$app->action = CoCo::$app->defaultAction;
                }
            }else if(count($uriPathArr) == 2){    // request /defaultModule/controller/action or /module/controller/defaultAction
                CoCo::$app->module = $defaultModule;
                CoCo::$app->controller = ucfirst($uriPathArr[0]);
                CoCo::$app->action = ucfirst($uriPathArr[1]);

                $className = 'app\\' . 'controllers\\' . CoCo::$app->controller . 'Controller';
                if(!class_exists($className)){
                    CoCo::$app->module = $uriPathArr[0];
                    CoCo::$app->controller = ucfirst($uriPathArr[1]);
                    CoCo::$app->action = CoCo::$app->defaultAction;
                }
            }else if(count($uriPathArr) == 3){  // request /module/controller/action
                CoCo::$app->module = $uriPathArr[0];
                CoCo::$app->controller = ucfirst($uriPathArr[1]);
                CoCo::$app->action = ucfirst($uriPathArr[2]);
            }else{  // request /module/controller/action/params0key/params0value/...
                CoCo::$app->module = $uriPathArr[0];
                CoCo::$app->controller = ucfirst($uriPathArr[1]);
                CoCo::$app->action = ucfirst($uriPathArr[2]);

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
    public function dispatch(){
        // Joining together the controller class name
        if(CoCo::$app->module == CoCo::$app->defaultModule){
            $className = 'app\\' . 'controllers\\' . CoCo::$app->controller . 'Controller';
        }else{  // Joining together the controller class name
            $className = 'app\\'  . 'controllers\\'. CoCo::$app->module.'\\' .CoCo::$app->controller . 'Controller';
        }

        // check controller class exists
        if(!class_exists($className)){
            header('HTTP/1.1 404 Not Found');
            header("status: 404 Not Found");
            throw new Exception('Page Not Found', 'Controller '.$className .' not exists!');
        }

        // Instantiate the controller
        $controllerObj = new $className();

        if(!(!is_null($controllerObj) && $controllerObj instanceof Controller)){
            header('HTTP/1.1 404 Not Found');
            header("status: 404 Not Found");
            throw new Exception('Page Not Found', 'Controller '.$className .' has error!');
        }

        $action = 'action'.CoCo::$app->action;

        // check action method exists
        if (!method_exists($controllerObj, $action)) {
            header('HTTP/1.1 404 Not Found');
            header("status: 404 Not Found");
            throw new Exception('Page Not Found','Action '.get_class($controllerObj) . '::' . $action . ' not exists!');
        }

        // action
        $controllerObj->$action();
    }

    public function end(){
        exit();
    }
}