<?php
/**
 * Web View
 * User: ttt
 * Date: 2015/11/23
 * Time: 23:02
 */

namespace coco\web;

use CoCo;
use coco\Exception;

class View extends \coco\base\View
{
    /**
     * page title
     * @var string
     */
    public $title = '';

    /**
     * @var
     */
    public $js;

    /**
     * @var
     */
    public $jsBottom;

    /**
     * @var
     */
    public $css;

    public $content = '';

    public $params = [];

    /**
     * layout
     * @var string
     */
    public $layout = 'main';

    /**
     * page file default extension
     * @var string
     */
    public $defaultExtension = 'phtml';

    /**
     * render a page with layout
     * @param string|null $view
     * @param array $data
     */
    public function render($view = null, $data = [])
    {
        if (empty($this->layout)) {
            $this->renderPartial($view, $data);
        } else {
            $layoutFile = CoCo::$app->appPath . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . strtolower($this->layout) . '.' . $this->defaultExtension;
            if (file_exists($layoutFile)) {
                $viewFile = $this->_parseView($view);
                $viewData = [];
                if (!empty($data)) {
                    foreach ($data as $k => $v) {
                        $$k = $v;
                        $viewData[$k] = $v;
                    }
                }
                if (!file_exists($viewFile)) {
                    try {
                        header('HTTP/1.1 500 Internal Server Error');
                        throw new Exception('View Not Found', 'View ' . $viewFile . ' not exists!' . PHP_EOL);
                    } catch (Exception $e) {
                        Debug::catchException($e);
                    }
                } else {
                    ob_start();
                    include $viewFile;
                    $content = ob_get_contents();
                    ob_end_clean();
                    $viewData['content'] = $content;
                    $this->renderPartial('/layouts/' . $this->layout, $viewData);
                }
            } else {
                $this->renderPartial($view, $data);
            }
        }
    }

    /**
     * render a page without layout
     * @param string|null $view
     * @param array $data
     */
    public function renderPartial($view = null, $data = [])
    {
        extract($data);
        $viewFile = $this->_parseView($view);
        try {
            if (file_exists($viewFile)) {
                include $viewFile;
            } else {
                header('HTTP/1.1 500 Internal Server Error');
                throw new Exception('View Not Found', 'View ' . $viewFile . ' not exists!' . PHP_EOL);
            }
        } catch (Exception $e) {
            Debug::catchException($e);
        }
    }

    /**
     * get view file
     * @param $view
     * @return string
     */
    protected function _parseView($view)
    {
        if (is_null($view)) {
            if (CoCo::$app->module == CoCo::$app->defaultModule) {
                $view = CoCo::$app->appPath . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . strtolower(CoCo::$app->controller) . DIRECTORY_SEPARATOR . strtolower(CoCo::$app->action) . '.' . $this->defaultExtension;
            } else {
                $view = CoCo::$app->appPath . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . CoCo::$app->module . DIRECTORY_SEPARATOR . strtolower(CoCo::$app->controller) . DIRECTORY_SEPARATOR . strtolower(CoCo::$app->action) . '.' . $this->defaultExtension;

            }
        } else {
            if (strpos($view, '/') === 0) { // /index/index
                $view = CoCo::$app->appPath . DIRECTORY_SEPARATOR . 'views' . strtolower($view) . '.' . $this->defaultExtension;
            } else {
                if (CoCo::$app->module == CoCo::$app->defaultModule) {
                    $view = CoCo::$app->appPath . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . strtolower(CoCo::$app->controller) . DIRECTORY_SEPARATOR . strtolower($view) . '.' . $this->defaultExtension;
                } else {
                    $view = CoCo::$app->appPath . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . CoCo::$app->module . DIRECTORY_SEPARATOR . strtolower(CoCo::$app->controller) . DIRECTORY_SEPARATOR . strtolower($view) . '.' . $this->defaultExtension;
                }
            }
        }
        return $this->_normalizePath($view);
    }

    protected function _normalizePath($path)
    {
        $parts = [];// Array to build a new path from the good parts
        $path = str_replace('\\', '/', $path);// Replace backslashes with forwardslashes
        $path = preg_replace('/\/+/', '/', $path);// Combine multiple slashes into a single slash
        $segments = explode('/', $path);// Collect path segments
        foreach ($segments as $segment) {
            if ($segment != '.') {
                $test = array_pop($parts);
                if (is_null($test))
                    $parts[] = $segment;
                else if ($segment == '..') {
                    if ($test == '..')
                        $parts[] = $test;

                    if ($test == '..' || $test == '')
                        $parts[] = $segment;
                } else {
                    $parts[] = $test;
                    $parts[] = $segment;
                }
            }
        }
        return implode(DIRECTORY_SEPARATOR, $parts);
    }

    public function addCss($file, $path = '')
    {
        if (empty($path)) {
            $path = pathinfo($_SERVER['SCRIPT_NAME']);
            $realFile = rtrim($path['dirname'], '/'). '/css/' . $file . '.css';
        }else{
            $realFile = $path . $file . '.css';
        }
        $this->css .= '<link rel="stylesheet" href="' . $realFile . '">';
    }

    public function addJs($file, $path = '')
    {
        if (empty($path)) {
            $path = pathinfo($_SERVER['SCRIPT_NAME']);
            $realFile = rtrim($path['dirname'], '/') . '/js/' . $file . '.js';
        }else{
            $realFile = $path . $file . '.js';
        }
        $this->js .= '<script type="text/javascript" src="' . $realFile . '"></script>';
    }

    public function addJsBottom($file, $path = '')
    {
        if (empty($path)) {
            $path = pathinfo($_SERVER['SCRIPT_NAME']);
            $realFile = rtrim($path['dirname'], '/') . '/js/' . $file . '.js';
        }else{
            $realFile = $path . $file . '.js';
        }
        $this->jsBottom .= '<script type="text/javascript" src="' . $realFile . '"></script>';
    }
}