<?php
/**
 * Created by PhpStorm.
 * User: ttt
 * Date: 2015/11/23
 * Time: 23:02
 */

namespace coco\web;

use CoCo;

class View extends \coco\base\View
{
    /**
     * page title
     * @var string
     */
    public $title = '';

    public $js;

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
    public $defaultExtension = 'php';

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
            $layoutFile = CoCo::$app->appPath . DIRECTORY_SEPARATOR . CoCo::$app->module . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . strtolower($this->layout) . '.' . $this->defaultExtension;
            if (file_exists($layoutFile)) {
                $viewFile = $this->_parseView($view);
                $viewData = [];
                if (!empty($data)) {
                    foreach ($data as $k => $v) {
                        $$k = $v;
                        $viewData[$k] = $v;
                    }
                }
                ob_start();
                include $viewFile;
                $content = ob_get_contents();
                ob_end_clean();
                $viewData['content'] = $content;
                $this->renderPartial('/layouts/' . $this->layout, $viewData);
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
                throw new \Exception('<h1 style="color: red;">View Not Found</h1><h2>View ' . $viewFile . ' not exists!</h2>' . PHP_EOL);
            }
        } catch (\Exception $e) {
            echo '<pre>';
            echo $e->getMessage();
            foreach ($e->getTrace() as $f) {
                echo $f['file'] . ' in line ' . $f['line'] . ' -> ' . $f['class'] . '::' . $f['function'] . PHP_EOL;
            }

            echo '<hr>' . date('Y-m-d H:i:s') . '  CoCo Framework ' . CoCo::getVersion() . ' </pre>';
        }
    }

    /**
     * get view file
     * @param $view
     * @return string
     */
    private function _parseView($view)
    {
        if (is_null($view)) {
            $view = CoCo::$app->appPath . DIRECTORY_SEPARATOR . CoCo::$app->module . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . strtolower(CoCo::$app->controller) . DIRECTORY_SEPARATOR . strtolower(CoCo::$app->action) . '.' . $this->defaultExtension;
        } else {
            if (strpos($view, '/') !== false) {
                $view = CoCo::$app->appPath . DIRECTORY_SEPARATOR . CoCo::$app->module . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . strtolower($view) . '.' . $this->defaultExtension;
            } else {
                $view = CoCo::$app->appPath . DIRECTORY_SEPARATOR . CoCo::$app->module . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . strtolower(CoCo::$app->controller) . DIRECTORY_SEPARATOR . strtolower($view) . '.' . $this->defaultExtension;
            }
        }
        return realpath($view);
    }
}