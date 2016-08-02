<?php
/**
 * Debug
 * User: ttt
 * Date: 2016/3/6
 * Time: 23:11
 */

namespace coco\web;

use CoCo;
use coco\Exception;

class Debug
{
    public static function catchException(Exception $e)
    {
        if (COCO_DEBUG) {
            $msg = '<pre class="debug">';
            //$msg .= '[' . $e->getCode() . '] ';
            $msg .= $e->getMessage();
            $msg .= '<p class="trace">' . $e->getTraceAsString() . PHP_EOL . '</p>';
            $msg .= '<hr><p class="copyright">' . date('Y-m-d H:i:s') . '  CoCo Framework ' . CoCo::getVersion() . ' &copy2014-' . date('Y') . '</p></pre>';
            echo '<style>.debug{font-family: "Inconsolata", "Fira Mono", "Source Code Pro", Monaco, Consolas, "Lucida Console", monospace}</style>';
            echo $msg;
        } else {
            $msg = $e->getMessage();
            //$msg .= PHP_EOL . '[' . $e->getCode() . '] ';
            $msg .= PHP_EOL . $e->getTraceAsString() . PHP_EOL;
            $msg .= date('Y-m-d H:i:s') . '  CoCo Framework ' . CoCo::getVersion() . ' &copy2014-' . date('Y') . PHP_EOL;

            $logFile = self::getLogFile();
            error_log(PHP_EOL . strip_tags($msg), 3, $logFile);
        }
    }

    public static function catchNormalException(\Exception $e)
    {
        if (COCO_DEBUG) {
            $msg = '<pre class="debug">';
            $msg .= '[' . $e->getCode() . '] ';
            $msg .= $e->getMessage();
            $msg .= '<p class="trace">' . $e->getTraceAsString() . PHP_EOL . '</p>';
            $msg .= '<hr><p class="copyright">' . date('Y-m-d H:i:s') . '  CoCo Framework ' . CoCo::getVersion() . ' &copy2014-' . date('Y') . '</p></pre>';
            echo '<style>.debug{font-family: "Inconsolata", "Fira Mono", "Source Code Pro", Monaco, Consolas, "Lucida Console", monospace}</style>';
            echo $msg;
        } else {
            $msg = $e->getMessage();
            $msg .= PHP_EOL . '[' . $e->getCode() . '] ';
            $msg .= PHP_EOL . $e->getTraceAsString() . PHP_EOL;
            $msg .= date('Y-m-d H:i:s') . '  CoCo Framework ' . CoCo::getVersion() . ' &copy2014-' . date('Y') . PHP_EOL;

            $logFile = self::getLogFile();
            error_log(PHP_EOL . strip_tags($msg), 3, $logFile);

        }
    }

    protected static function getLogFile()
    {
        if (!empty(CoCo::$app->config['log']['path'])) {
            return CoCo::$app->config['log']['path'] . '/app.log';
        } else {
            return dirname(CoCo::$app->appPath) . '/runtime/logs/app.log';
        }
    }

}
