<?php

namespace coco\web;

use CoCo;
use coco\base\ExitException;

/**
 * Created by PhpStorm.
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
            CoCo::$app = $this;
        }
    }

    public function run()
    {
        if(!empty(CoCo::$app->config['appPath']) && is_dir(CoCo::$app->config['appPath'])){
            CoCo::$app->appPath = CoCo::$app->config['appPath'];
            \ClassLoader::$basePath = dirname(CoCo::$app->config['appPath']);
        }else{
            throw new \Exception('<pre><h1 style="color: red;">Configuration Error</h1>Configuration "appPath" is required!<hr>'.date('Y-m-d H:i:s').'  CoCo Framework v0.2 </pre>');
        }

        \ClassLoader::addPrefix('app\\', 'app/');

        $defaultModule = !empty(CoCo::$app->config['defaultModule']) ? CoCo::$app->config['defaultModule'] : 'index';
        $defaultController = !empty(CoCo::$app->config['defaultController']) ? CoCo::$app->config['defaultController'] : 'Index';
        $defaultAction = !empty(CoCo::$app->config['defaultAction']) ? CoCo::$app->config['defaultAction'] : 'Index';

        $urlType = !empty(CoCo::$app->config['url']['type']) ? CoCo::$app->config['url']['type'] : 'normal';

        if ($urlType == 'path') { //path
            $requestPath = $_SERVER['REQUEST_URI'];
            if(stripos($_SERVER['REQUEST_URI'], $_SERVER['SCRIPT_NAME']) === 0){
                $requestPath = substr($_SERVER['REQUEST_URI'], strlen($_SERVER['SCRIPT_NAME']));
            }
            if(strpos($requestPath, '?') !== false){
                $requestPath = strstr($requestPath, '?', true);
            }
            if (!empty(CoCo::$app->config['url']['suffix'])) {
                // remove the url suffix
                if(strpos($requestPath, CoCo::$app->config['url']['suffix']) !== false){
                    $requestPath = strstr($requestPath, CoCo::$app->config['url']['suffix'], true);
                }
            }
            $requestPath = ltrim($requestPath, '/');
            $requestPathArr = explode('/', $requestPath);

            $request['module'] = !empty($requestPathArr[0]) ? strtolower($requestPathArr[0]) : strtolower($defaultModule);
            $request['controller'] = !empty($requestPathArr[1]) ? ucfirst($requestPathArr[1]) : ucfirst($defaultController);
            $request['action'] = !empty($requestPathArr[2]) ? ucfirst($requestPathArr[2]) : ucfirst($defaultAction);

            $arrCount = count($requestPathArr);
            //把参数放入get
            if ($arrCount > 3) {
                for ($i = 3; $i < $arrCount; $i += 2) {
                    if (isset($requestPathArr[($i + 1)])) {
                        $_GET[$requestPathArr[$i]] = $requestPathArr[($i + 1)];
                    } else {
                        $_GET[$requestPathArr[$i]] = '';
                    }
                }
            }
        } else { //normal
            $request['module'] = !empty($_GET['m']) ? strtolower($_GET['m']) : strtolower($defaultModule);
            $request['controller'] = !empty($_GET['c']) ? ucfirst($_GET['c']) : ucfirst($defaultController);
            $request['action'] = !empty($_GET['a']) ? ucfirst($_GET['a']) : ucfirst($defaultAction);
        }

        CoCo::$app->module = $request['module'];
        CoCo::$app->controller = $request['controller'];
        CoCo::$app->action = $request['action'];

        // Joining together the controller class name
        $className = 'app\\' . CoCo::$app->module . '\\controllers\\' . $request['controller'] . 'Controller';

        // check controller class exists
        if(!class_exists($className)){
            header('HTTP/1.1 404 Not Found');
            header("status: 404 Not Found");
            throw new \Exception('<pre><h1 style="color: red;">Page Not Found</h1>Controller '.$className .' not exists!<hr>'.date('Y-m-d H:i:s').'  CoCo Framework v0.2 </pre>');
        }
        // Instantiate the controller
        $controllerObj = new $className();

        $action = 'action'.$request['action'];

        // check action method exists
        if (!method_exists($controllerObj, $action)) {
            header('HTTP/1.1 404 Not Found');
            header("status: 404 Not Found");
            throw new \Exception('<pre><h1 style="color: red;">Page Not Found</h1>Method '.get_class($controllerObj) . '::' . $action . ' not exists!<hr>'.date('Y-m-d H:i:s').'  CoCo Framework v0.2 </pre>');
        }

        //调用action
        $controllerObj->$action();
    }
}