<?php
/**
 * Created by PhpStorm.
 * User: ttt
 * Date: 2015/11/21
 * Time: 23:17
 */

namespace coco\web;
use CoCo;

class Controller extends \coco\base\Controller
{
    /**
     * @var null
     */
    private static $_view = null;

    /**
     * @return object View
     */
    public function getView(){
        if(is_null(self::$_view)){
            self::$_view = new View();
        }
        return self::$_view;
    }

    /**
     * create a relative Url
     * @param string $path
     * @param array $params
     * @return string
     */
    public function createUrl($path, $params = [])
    {
        if(empty($path)){
            return '';
        }
        $url = '';
        if(isset(CoCo::$app->config['url']['type']) && CoCo::$app->config['url']['type'] == 'path'){
            if(strpos($path, '/') === 0){ // /home/index/index
                if($path == '/'){
                    $url = '/'.CoCo::$app->config['defaultModule'].'/'.CoCo::$app->config['defaultController'].'/'.CoCo::$app->config['defaultAction'];
                }else{
                    $pathArr = explode('/', ltrim($path, '/'));
                    $count = count($pathArr);
                    if($count == 3){    // module + controller + action
                        $url = '/'.$pathArr[0].'/'.$pathArr[1].'/'.$pathArr[2];
                    }else if($count == 2){ // module + controller + defaultAction
                        $url = '/'.$pathArr[0].'/'.$pathArr[1].'/'.CoCo::$app->config['defaultAction'];
                    }else if($count == 1){ // module + defaultController + defaultAction
                        $url = '/'.$pathArr[0].'/'.CoCo::$app->config['defaultController'].'/'.CoCo::$app->config['defaultAction'];
                    }
                }
            }else{ // home/index
                $pathArr = explode('/', $path);
                $count = count($pathArr);
                if($count == 1){      //current module + current controller + action
                    $url = '/'.CoCo::$app->module.'/'.CoCo::$app->controller.'/'.$pathArr[0];
                }else if($count == 2){// current module + controller + action
                    $url = '/'.CoCo::$app->module.'/'.$pathArr[0].'/'.$pathArr[1];
                }else if($count == 3){// module + controller + action
                    $url = '/'.$pathArr[0].'/'.$pathArr[1].'/'.$pathArr[2];
                }
            }
            if(!empty($params)){
                foreach($params as $k=>$v){
                    $url .= "/$k/$v";
                }
            }
            // url suffix
            if(!empty(CoCo::$app->config['url']['suffix'])){
                $url .= CoCo::$app->config['url']['suffix'];
            }
        }else{
            // /home/index/index
            if(strpos($path, '/') === 0){ // /home/index/index
                if($path == '/'){
                    $url = '?m='.CoCo::$app->config['defaultModule'].'&c='.CoCo::$app->config['defaultController'].'&a='.CoCo::$app->config['defaultAction'];
                }else{
                    $pathArr = explode('/', ltrim($path, '/'));
                    $count = count($pathArr);
                    if($count == 3){    // module + controller + action
                        $url = '?m='.$pathArr[0].'&c='.$pathArr[1].'&a='.$pathArr[2];
                    }else if($count == 2){ // module + controller + defaultAction
                        $url = '?m='.$pathArr[0].'&c='.$pathArr[1].'&a='.CoCo::$app->config['defaultAction'];
                    }else if($count == 1){ // module + defaultController + defaultAction
                        $url = '?m='.$pathArr[0].'&c='.CoCo::$app->config['defaultController'].'&a='.CoCo::$app->config['defaultAction'];
                    }
                }
            }else{ // home/index
                $pathArr = explode('/', $path);
                $count = count($pathArr);
                if($count == 1){      //current module + current controller + action
                    $url = '?m='.CoCo::$app->module.'&c='.CoCo::$app->controller.'&a='.$pathArr[0];
                }else if($count == 2){// current module + controller + action
                    $url = '?m='.CoCo::$app->module.'&c='.$pathArr[0].'&a='.$pathArr[1];
                }else if($count == 3){// module + controller + action
                    $url = '?m='.$pathArr[0].'&c='.$pathArr[1].'&a='.$pathArr[2];
                }
            }

            if(!empty($params)){
                foreach($params as $k=>$v){
                    $url .= "&$k=$v";
                }
            }
        }
        if(!empty(CoCo::$app->config['url']['showScript'])){
            $url = $_SERVER['SCRIPT_NAME'].$url;
        }
        return $url;
    }
}