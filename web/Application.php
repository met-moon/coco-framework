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
        try{
            \ClassLoader::$basePath = dirname(CoCo::$app->config['appPath']);
            \ClassLoader::addPrefix('app\\', 'app/');

            $defaultModule = !empty(CoCo::$app->config['defaultModule']) ? CoCo::$app->config['defaultModule'] : 'index';
            $defaultController = !empty(CoCo::$app->config['defaultController']) ? CoCo::$app->config['defaultController'] : 'Index';
            $defaultAction = !empty(CoCo::$app->config['defaultAction']) ? CoCo::$app->config['defaultAction'] : 'Index';

            $urlType = !empty(CoCo::$app->config['url']['type']) ? CoCo::$app->config['url']['type'] : 'normal';

            if ($urlType == 'path') { //path
                $requestUri = $_SERVER['REQUEST_URI'];
                if (!empty(CoCo::$app->config['url']['suffix'])) {
                    //TODO 去掉尾缀
                    $request_uri = str_replace(CoCo::$app->config['url']['suffix'], '', $requestUri);
                }
                $requestUri = ltrim($requestUri, '/');
                $requestUriArr = explode('/', $requestUri);

                if (!empty($request_uri) && strpos('index.php', $request_uri) === 0) {
                    $request = self::normalUrl($defaultModule, $defaultController, $defaultAction);
                } else {
                    $request['module'] = !empty($requestUriArr[0]) ? strtolower($requestUriArr[0]) : strtolower($defaultModule);
                    $request['controller'] = !empty($requestUriArr[1]) ? ucfirst($requestUriArr[1]) : ucfirst($defaultController);
                    $request['action'] = 'action' . (!empty($requestUriArr[2]) ? ucfirst($requestUriArr[2]) : ucfirst($defaultAction));
                }

                $arrCount = count($requestUriArr);
                //把参数放入get
                if ($arrCount > 3) {
                    for ($i = 3; $i < $arrCount; $i += 2) {
                        if (isset($requestUriArr[($i + 1)])) {
                            $_GET[$requestUriArr[$i]] = $requestUriArr[($i + 1)];
                        } else {
                            $_GET[$requestUriArr[$i]] = '';
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

            //拼接控制器类名
            $className = 'app\\' . CoCo::$app->module . '\\controllers\\' . $request['controller'] . 'Controller';

            //实例化控制器
            $controllerObj = new $className();


            $action = 'action'.$request['action'];

            //检查是否存在此action方法
            if (!method_exists($controllerObj, $action)) {
                die(get_class($controllerObj) . '类不存在' . $action . '方法!');
            }

            //调用action
            $controllerObj->$action();
        } catch (ExitException $e) {

        }
    }

    public function end(){

    }
}