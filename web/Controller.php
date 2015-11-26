<?php
/**
 * Created by PhpStorm.
 * User: ttt
 * Date: 2015/11/21
 * Time: 23:17
 */

/*
│││││││││││││││││││││││││││││││││││││││││││││╲ ︶︶  ︶︶  ︶︶
最美的不是下雨天                                 ┃   ┆  ┆     ┆
       是曾与你躲过雨的屋檐                      ┃          ┆
            ——周杰伦《不能说的秘密》    /●  ●    ┃ ┆   ┆     ┆
┳┳┳┳┳┳┳┳┳┳┳┳┳┳┳┳┳┳┳┳┳ /▲\/■> ┳┫    ┆   ┆    ┆
┻┻┻┻┻┻┻┻┻┻┻┻┻┻┻┻┻┻┻┻   >| ||   ┻   ┆   ┆   ┆

*/

namespace coco\web;

use CoCo;

class Controller extends \coco\base\Controller
{
    /**
     * @var null
     */
    private static $_view = null;

    public function init()
    {
    }


    /**
     * @return object View
     */
    public function getView()
    {
        if (is_null(self::$_view)) {
            self::$_view = new View();
        }
        return self::$_view;
    }

    public function render($view = null, $data = [])
    {
        $this->getView()->render($view, $data);
    }

    public function renderPartial($view = null, $data = [])
    {
        $this->getView()->renderPartial($view, $data);
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
        $url = '';
        if (isset(CoCo::$app->config['url']['type']) && CoCo::$app->config['url']['type'] == 'path') {
            if (strpos($path, '/') === 0) { // /home/index/index
                if ($path == '/') {
                    $url = '/' . CoCo::$app->config['defaultModule'] . '/' . CoCo::$app->config['defaultController'] . '/' . CoCo::$app->config['defaultAction'];
                } else {
                    $pathArr = explode('/', ltrim($path, '/'));
                    $count = count($pathArr);
                    if ($count == 3) {    // module + controller + action
                        $url = '/' . $pathArr[0] . '/' . $pathArr[1] . '/' . $pathArr[2];
                    } else if ($count == 2) { // module + controller + defaultAction
                        $url = '/' . $pathArr[0] . '/' . $pathArr[1] . '/' . CoCo::$app->config['defaultAction'];
                    } else if ($count == 1) { // module + defaultController + defaultAction
                        $url = '/' . $pathArr[0] . '/' . CoCo::$app->config['defaultController'] . '/' . CoCo::$app->config['defaultAction'];
                    }
                }
            } else { // home/index
                $pathArr = explode('/', $path);
                $count = count($pathArr);
                if ($count == 1) {      //current module + current controller + action
                    $url = '/' . CoCo::$app->module . '/' . CoCo::$app->controller . '/' . $pathArr[0];
                } else if ($count == 2) {// current module + controller + action
                    $url = '/' . CoCo::$app->module . '/' . $pathArr[0] . '/' . $pathArr[1];
                } else if ($count == 3) {// module + controller + action
                    $url = '/' . $pathArr[0] . '/' . $pathArr[1] . '/' . $pathArr[2];
                }
            }
            $url = strtolower($url);
            if (!empty($params)) {
                foreach ($params as $k => $v) {
                    $url .= "/$k/$v";
                }
            }
            // url suffix
            if (!empty(CoCo::$app->config['url']['suffix'])) {
                $url .= CoCo::$app->config['url']['suffix'];
            }
        } else {
            // /home/index/index
            if (strpos($path, '/') === 0) { // /home/index/index
                if ($path == '/') {
                    $url = '?m=' . CoCo::$app->config['defaultModule'] . '&c=' . CoCo::$app->config['defaultController'] . '&a=' . CoCo::$app->config['defaultAction'];
                } else {
                    $pathArr = explode('/', ltrim($path, '/'));
                    $count = count($pathArr);
                    if ($count == 3) {    // module + controller + action
                        $url = '?m=' . $pathArr[0] . '&c=' . $pathArr[1] . '&a=' . $pathArr[2];
                    } else if ($count == 2) { // module + controller + defaultAction
                        $url = '?m=' . $pathArr[0] . '&c=' . $pathArr[1] . '&a=' . CoCo::$app->config['defaultAction'];
                    } else if ($count == 1) { // module + defaultController + defaultAction
                        $url = '?m=' . $pathArr[0] . '&c=' . CoCo::$app->config['defaultController'] . '&a=' . CoCo::$app->config['defaultAction'];
                    }
                }
            } else { // home/index
                $pathArr = explode('/', $path);
                $count = count($pathArr);
                if ($count == 1) {      //current module + current controller + action
                    $url = '?m=' . CoCo::$app->module . '&c=' . CoCo::$app->controller . '&a=' . $pathArr[0];
                } else if ($count == 2) {// current module + controller + action
                    $url = '?m=' . CoCo::$app->module . '&c=' . $pathArr[0] . '&a=' . $pathArr[1];
                } else if ($count == 3) {// module + controller + action
                    $url = '?m=' . $pathArr[0] . '&c=' . $pathArr[1] . '&a=' . $pathArr[2];
                }
            }
            $url = strtolower($url);

            if (!empty($params)) {
                foreach ($params as $k => $v) {
                    $url .= "&$k=$v";
                }
            }
        }
        if (!empty(CoCo::$app->config['url']['showScript'])) {
            $url = $_SERVER['SCRIPT_NAME'] . $url;
        }

        return $url;
    }

    /**
     * redirect
     * @param string $path
     * @param array $params
     * @param int $time
     * @param string $msg
     */
    public function redirect($path, $params = [], $time = 0, $msg = '')
    {
        if (strpos($path, 'http://', 0) !== false || strpos($path, 'https://', 0) !== false) {
            $url = $path;
        } else {
            $url = $this->createUrl($path, $params);
            if (empty($url)) {
                return;
            }
        }
        if(empty($msg)){
            $msg = "系统将在{$time}秒之后自动跳转到{$url}！";
        }
        if (!headers_sent()) {
            if (0 === $time) {
                header('Location: ' . $url);
            } else {
                header("refresh:{$time};url={$url}");
                echo($msg);
            }
            exit();
        } else {
            $str  = "<meta http-equiv='Refresh' content='{$time};URL={$url}'>";
            if ($time != 0){
                $str .= $msg;
            }
            exit($str);
        }
    }

    /**
     * 获取get方法提交的值
     * @param string $key $_GET参数
     * @param mixed $default_value 没有值时给予默认值
     * @return mixed
     */
    public function getQuery($key,$default_value = null){
        if(isset($_GET[$key])){
            return $_GET[$key];
        }else if(isset($default_value)){
            return $default_value;
        }else{
            return null;
        }
    }

    /**
     * 获取post方法提交的值
     * @param string $key $_POST参数
     * @param mixed $default_value 没有值时给予默认值
     * @return mixed
     */
    public function getPost($key,$default_value = null){
        if(isset($_POST[$key])){
            return $_POST[$key];
        }else if(isset($default_value)){
            return $default_value;
        }else{
            return null;
        }
    }

    /**
     * $_REQUEST 默认情况下包含了 $_GET，$_POST 和 $_COOKIE 的数组
     * （这个数组的项目及其顺序依赖于 PHP 的 variables_order 指令的配置。）
     * 不建议使用
     * @param string $key $_REQUEST参数
     * @param mixed $default_value 没有值时给予默认值
     * @return mixed
     */
    public function getParam($key,$default_value = null){
        if(isset($_REQUEST[$key])){
            return $_REQUEST[$key];
        }else if(isset($default_value)){
            return $default_value;
        }else{
            return null;
        }
    }

    /**
     * echo json_encode data
     * @param array $data
     */
    public function echoJson($data){
        echo json_encode($data);
        CoCo::$app->end();
    }
}