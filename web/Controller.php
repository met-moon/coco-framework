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

use coco\CoCo;

class Controller extends \coco\base\Controller
{
    /**
     * @var TwigView
     */
    protected $view;

    /**
     * @return TwigView
     */
    public function getView()
    {
        if (!is_null($this->view) && $this->view instanceof TwigView) {
            return $this->view;
        }
        return $this->view = new TwigView();
    }

    /**
     * this is a shortcut for View render()
     * @param string $view
     * @param array $data
     * @return string
     */
    public function render($view, array $data = [])
    {
        return $this->getView()->render($view, $data);
    }

    /**
     * redirect
     * @param string $path
     * @param array $params
     */
    public function redirect($path, $params = [])
    {
        //TODO
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public function getRequest(){
        return CoCo::$app->getRequest();
    }
}
