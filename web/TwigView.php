<?php
namespace coco\web;
use coco\CoCo;


/**
 * Twig View
 * User: ttt
 * Date: 2017/2/15
 * Time: 16:38
 */
class TwigView
{
    public function render($view, array $data = [])
    {
        $loader = new \Twig_Loader_Filesystem(CoCo::$app->getAppPath().'/views');
        $twig = new \Twig_Environment($loader, array(
            'cache' => CoCo::$app->getRootPath().'/runtime/cache/twig',
            'debug' => CoCo::$app->getDebug(),
            'charset' => CoCo::$app->getCharset(),
        ));

        $view .= '.twig';

        return $twig->render($view, $data);
    }
}