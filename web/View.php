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

class View
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
     * view file path
     * @var string
     */
    public $viewPath = '';

    /**
     * template file extension
     * @var string
     */
    public $ext = 'phtml';

    public $controller;

    public function __construct()
    {
        $this->getViewPath();
    }

    public function getController()
    {
        return CoCo::$app->currentController;
    }

    /**
     * render a page with layout
     * @param string|null $view
     * @param array $data
     */
    public function render($view = null, array $data = [])
    {
        if (empty($this->layout)) {
            $this->renderPartial($view, $data);
        } else {
            $layoutFile = $this->viewPath . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . $this->layout . '.' . $this->ext;
            if (file_exists($layoutFile)) {
                $viewFile = $this->parseView($view);
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
                    $this->includeFile($viewFile);
                    $content = ob_get_contents();
                    ob_end_clean();
                    $viewData['content'] = $content;
                    $this->renderPartial('layouts/' . $this->layout, $viewData);
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
    public function renderPartial($view = null, array $data = [])
    {
        extract($data);

        $viewFile = $this->parseView($view);

        try {
            if (file_exists($viewFile)) {
                $this->includeFile($viewFile);
            } else {
                header('HTTP/1.1 500 Internal Server Error');
                throw new Exception('View Not Found', 'View ' . $viewFile . ' not exists!' . PHP_EOL);
            }
        } catch (Exception $e) {
            Debug::catchException($e);
        }
    }

    /**
     * get view file path
     * @return string
     */
    protected function getViewPath()
    {
        if (CoCo::$app->module == CoCo::$app->defaultModule) {
            $this->viewPath = '@app/views';
            if (!empty(CoCo::$app->config['viewPath'])) {
                $this->viewPath = CoCo::$app->config['viewPath'];
            }
        } else {
            $this->viewPath = '@app/modules/' . CoCo::$app->module . '/views';
            if (!empty(CoCo::$app->config['modules'][CoCo::$app->module]['viewPath'])) {
                $this->viewPath = CoCo::$app->config['modules'][CoCo::$app->module]['viewPath'];
            }
        }

        return $this->viewPath = str_replace('@app', CoCo::$app->appPath, $this->viewPath);
    }

    /**
     * parse view file
     * @param string $view
     * @return string file path
     */
    protected function parseView($view)
    {
        if (is_null($view)) {
            $view = $this->viewPath . DIRECTORY_SEPARATOR . CoCo::$app->controller . DIRECTORY_SEPARATOR . CoCo::$app->action . '.' . $this->ext;
        } else {
            if (strpos($view, '/') > 0) {
                $view = $this->viewPath . DIRECTORY_SEPARATOR . $view . '.' . $this->ext;
            } else {
                $view = $this->viewPath . DIRECTORY_SEPARATOR . CoCo::$app->controller . DIRECTORY_SEPARATOR . $view . '.' . $this->ext;
            }
        }
        return $this->normalizePath($view);
    }

    protected function normalizePath($path)
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
            $realFile = $this->publicPath() . 'css/' . $file . '.css';
        } else {
            $realFile = $path . $file . '.css';
        }
        $this->css .= '<link rel="stylesheet" href="' . $realFile . '">';
    }

    public function addJs($file, $path = '')
    {
        if (empty($path)) {
            $realFile = $this->publicPath() . 'js/' . $file . '.js';
        } else {
            $realFile = $path . $file . '.js';
        }
        $this->js .= '<script type="text/javascript" src="' . $realFile . '"></script>';
    }

    public function addJsBottom($file, $path = '')
    {
        if (empty($path)) {
            $realFile = $this->publicPath() . 'js/' . $file . '.js';

        } else {
            $realFile = $path . $file . '.js';
        }
        $this->jsBottom .= '<script type="text/javascript" src="' . $realFile . '"></script>';
    }

    public function publicPath()
    {
        $basePath = CoCo::$app->basePath();
        return rtrim($basePath, '/');
    }

    /**
     * include file
     * @param string $file
     * @return mixed
     */
    protected function includeFile($file)
    {
        include $file;
    }
}