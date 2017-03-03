<?php
namespace coco\web;

use coco\routing\DynamicRoute;
use Symfony\Component\HttpFoundation\Request;

/**
 * Web Application
 * User: ttt
 * Date: 2015/11/21
 * Time: 20:15
 */
class Application extends \coco\base\Application
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var DynamicRoute
     */
    protected $route;

    /**
     * @return Request
     */
    public function getRequest()
    {
        if (is_null($this->request)) {
            $this->request = Request::createFromGlobals();
        }
        return $this->request;
    }

    /**
     * Bootstrap
     * @return $this
     */
    public function bootstrap()
    {
        //TODO

        return $this;
    }

    /**
     * Run the Application
     */
    public function run()
    {
        $this->startSession();
        $this->request = $this->getRequest();
        $this->route = new DynamicRoute($this->config);
        $response = $this->route->dispatch($this->request);
        $response->setCharset($this->charset)->send();
    }

    /**
     * start session
     */
    protected function startSession()
    {
        if (isset($this->config['session']['start']) && $this->config['session']['start'] === false) {
            return;
        }
        if (!empty($this->config['session']['name'])) {
            session_name($this->config['session']['name']);
        }
        session_start();
    }
}
