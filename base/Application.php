<?php

namespace coco\base;

use coco\CoCo;
use coco\config\Config;
use coco\Exception;

/**
 * Base Application
 * @property \coco\db\Connection $db The database connection. This property is read-only.
 * User: ttt
 * Date: 2015/11/21
 * Time: 22:10
 */
class Application
{
    protected $debug = false;
    protected $env = 'prod';   // prod dev test

    /**
     * @var string
     */
    protected $rootPath;

    /**
     * @var string
     */
    protected $appPath;

    /**
     * @var string
     */
    protected $configPath;

    protected $config;

    protected $timezone = 'UTC';
    protected $charset = 'UTF-8';

    /**
     * Application constructor.
     * @param $rootPath
     * @param null $configPath
     * @param null $appPath
     * @throws Exception
     */
    public function __construct($rootPath, $configPath = null, $appPath = null)
    {
        if (!is_dir($rootPath)) {
            throw new Exception("The application constructor's parameter `rootPath` is not a valid directory");
        }
        $this->rootPath = realpath($rootPath);

        if (is_null($configPath)) {
            $this->configPath = $this->rootPath . DIRECTORY_SEPARATOR . 'config';
        } else {
            if (!is_dir($configPath)) {
                throw new Exception("The application constructor's parameter `configPath` is not a valid directory");
            }
            $this->configPath = realpath($configPath);
        }

        if (is_null($appPath)) {
            $this->appPath = $this->rootPath . DIRECTORY_SEPARATOR . 'app';
        } else {
            if (!is_dir($appPath)) {
                throw new Exception("The application constructor's parameter `appPath` is not a valid directory");
            }
            $this->appPath = realpath($appPath);
        }

        Config::setConfigDir($this->configPath);

        $this->config = Config::get('app');

        if (!empty($this->config['timezone'])) {
            $this->timezone = $this->config['timezone'];
            date_default_timezone_set($this->timezone);
        } else {
            $this->timezone = date_default_timezone_get();
        }

        if (!empty($this->config['charset'])) {
            $this->charset = $this->config['charset'];
        } else {
            $this->charset = 'UTF-8';
        }

        CoCo::$app = $this;
    }

    public function __call($name, $arguments)
    {
        if (strpos($name, 'get') === 0) { //get protected attribute
            $attribute = lcfirst(substr($name, 3));
            if (isset($this->$attribute)) {
                return $this->$attribute;
            }
        }
        throw new Exception('Call to undefined method ' . get_class($this) . '::' . $name . '()');
    }

    public function __get($name)
    {
        if (isset($this->config['components'][$name])) {
            $params = $this->config['components'][$name];
            $className = $params['class'];
            unset($params['class']);
            $this->$name = new $className();
            if (!empty($params)) {
                foreach ($params as $attribute => $value) {
                    $this->$name->$attribute = $value;
                }
            }
            return $this->$name;
        } else {
            throw new Exception('Undefined property: '.get_class($this).'::$'.$name);
        }
    }

    /**
     * enable debug
     * @return $this
     */
    public function enableDebug()
    {
        $this->debug = true;
        return $this;
    }

    /**
     * the end
     * @param int $code
     */
    public function end($code = 0)
    {
        exit($code);
    }
}
