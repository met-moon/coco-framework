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
    public $layout = 'main';
    public $viewExt = 'php';

    private $_view;

    public function getView(){
        return $this->_view;
    }

    public function render($view, $data)
    {

    }

    /**
     * @param string $view
     * @param array $data
     */
    public function renderPartial($view = null, $data = [])
    {
        extract($data);
        if(is_null($view)){
            $view = CoCo::$app->appPath.DIRECTORY_SEPARATOR.CoCo::$app->module.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.strtolower(CoCo::$app->controller).DIRECTORY_SEPARATOR.strtolower(CoCo::$app->action).'.'.$this->viewExt;
            if(file_exists($view)){
                include $view;
            }
        }
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function assign($key, $value){

    }

    public function display($view = null){

    }

    public function createUrl()
    {

    }
}