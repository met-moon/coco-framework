<?php
namespace coco\routing;

use coco\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * User: ttt
 * Date: 2017/2/14
 * Time: 10:43
 */
class DynamicRoute
{
    protected $uri;
    protected $method;

    protected $defaultModule = 'index';
    protected $defaultController = 'index';
    protected $defaultAction = 'index';

    protected $controllerNamespace = 'app\\controllers';

    protected $module;
    protected $controller;
    protected $action;

    protected $params = [];

    protected $controllerClassName;

    protected $urlSuffix;

    protected $modulesConfig;

    public function __construct($config)
    {
        if (!empty($config['defaultModule'])) {
            $this->defaultModule = $config['defaultModule'];
        }

        if (!empty($config['defaultController'])) {
            $this->defaultController = $config['defaultController'];
        }

        if (!empty($config['defaultAction'])) {
            $this->defaultAction = $config['defaultAction'];
        }

        if (!empty($config['urlSuffix'])) {
            $this->urlSuffix = $config['urlSuffix'];
        }

        if (!empty($config['controllerNamespace'])) {
            $this->controllerNamespace = $config['controllerNamespace'];
        }

        if (!empty($config['modules'])) {
            $this->modulesConfig = $config['modules'];
        }
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function dispatch(Request $request)
    {
        return $this->parseRequest($request)->handlerRequest();
    }

    /**
     * @return Response
     */
    protected function handlerRequest(){

        if(empty($this->controllerClassName) || !class_exists($this->controllerClassName)){
            //echo '404';
            return Response::create("404 not found", 404);
        }

        $controllerObj = new $this->controllerClassName;

        $actionName = $this->action.'Action';

        if(!method_exists($controllerObj, $actionName)){
            //echo '404';
            return Response::create("404 not found", 404);
        }

        $result = $controllerObj->$actionName();

        if(is_array($result) || is_object($result)){
            return new JsonResponse($result);
        }

        return new Response($result);
    }

    protected function parseRequest(Request $request){
        $this->method = $request->getMethod();
        $this->uri = $request->getPathInfo();

        $uri = $this->uri;

        if (!empty($this->urlSuffix) && strstr($uri, $this->urlSuffix) === $this->urlSuffix) {
            $uri = strstr($uri, $this->urlSuffix, true);
        }

        $uri = trim($uri, '/').'/';

        if ($uri === '/') {
            $this->controllerClassName = $this->controllerNamespace . '\\' . ucfirst($this->defaultController) . 'Controller';
            $this->module = $this->defaultModule;
            $this->controller = $this->defaultController;
            $this->action = $this->defaultAction;
        } else {
            $uriPathArr = explode('/', trim($uri, '/'));
            if (isset($uriPathArr[0])) {
                $className = $this->controllerNamespace . '\\' . ucfirst($uriPathArr[0]) . 'Controller';
                if (class_exists($className)) {
                    $this->controllerClassName = $className;
                    $this->module = $this->defaultModule;
                    $this->controller = ucfirst($uriPathArr[0]);
                    $this->action = isset($uriPathArr[1]) ? $uriPathArr[1] : $this->defaultAction;
                } else {
                    $moduleName = $uriPathArr[0];
                    if (in_array($moduleName, $this->modulesConfig) || isset($this->modulesConfig[$moduleName])) {   //module
                        $moduleConfig = isset($this->modulesConfig[$moduleName]) ? $this->modulesConfig[$moduleName] : [];
                        if (isset($moduleConfig['controllerNamespace'])) {
                            $className = $moduleConfig['controllerNamespace'];
                        } else {
                            $className = 'app\\modules\\' . $moduleName . '\\controllers';
                        }
                        if (isset($uriPathArr[1])) {  //controller
                            $controllerName = $uriPathArr[1];
                        } else {
                            $controllerName = isset($moduleConfig['defaultController'])
                                ? $moduleConfig['defaultController'] : $this->defaultController;
                        }
                        $className .= '\\' . ucfirst($controllerName) . 'Controller';
                        if (class_exists($className)) {
                            $this->controllerClassName = $className;
                            $this->module = $moduleName;
                            $this->controller = $className;
                            if (isset($uriPathArr[2])) {  //action
                                $this->action = $uriPathArr[2];
                            } else {
                                $this->action = isset($moduleConfig['defaultAction'])
                                    ? $moduleConfig['defaultAction'] : $this->defaultAction;
                            }
                        }
                    }
                }
            }
        }

        return $this;
    }

    public function __call($name, $arguments)
    {
        if (strpos($name, 'get') === 0) { //get protected attribute
            $attribute = lcfirst(substr($name, 3));
            if (isset($this->$attribute)) {
                return $this->$attribute;
            }
        }
        throw new Exception('Call to undefined method ' . get_class($this) . '::' . $name . '()');
    }
}
