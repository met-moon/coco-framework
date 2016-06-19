<?php
/**
 * Web Controller
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
     * @var View|null
     */
    protected $_view = null;

    /**
     * @return View
     */
    public function getView()
    {
        if (!is_null($this->_view) && $this->_view instanceof View) {
            return $this->_view;
        }
        return $this->_view = new View();
    }

    /**
     * this is a shortcut for View render()
     * @param null|string $view
     * @param array $data
     */
    public function render($view = null, $data = [])
    {
        $this->getView()->render($view, $data);
    }

    /**
     * this is a shortcut for View renderPartial()
     * @param null|string $view
     * @param array $data
     */

    public function renderPartial($view = null, $data = [])
    {
        $this->getView()->renderPartial($view, $data);
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
            $url = CoCo::$app->createUrl($path, $params);
            if (empty($url)) {
                return;
            }
        }
        if (empty($msg)) {
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
            $str = "<meta http-equiv='Refresh' content='{$time};URL={$url}'>";
            if ($time != 0) {
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
    public function getQuery($key, $default_value = null)
    {
        return isset($_GET[$key]) ? $_GET[$key] : $default_value;
    }

    /**
     * 获取post方法提交的值
     * @param string $key $_POST参数
     * @param mixed $default_value 没有值时给予默认值
     * @return mixed
     */
    public function getPost($key, $default_value = null)
    {
        return isset($_POST[$key]) ? $_POST[$key] : $default_value;
    }

    /**
     * $_REQUEST 默认情况下包含了 $_GET，$_POST 和 $_COOKIE 的数组
     * （这个数组的项目及其顺序依赖于 PHP 的 variables_order 指令的配置。）
     * 不建议使用
     * @param string $key $_REQUEST参数
     * @param mixed $default_value 没有值时给予默认值
     * @return mixed
     */
    public function getParam($key, $default_value = null)
    {
        return isset($_POST[$key]) ? $_POST[$key] : $default_value;
    }
}
