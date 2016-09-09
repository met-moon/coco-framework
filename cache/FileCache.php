<?php
namespace coco\cache;

use coco\Exception;

/**
 * FileCache
 * User: ttt
 * Date: 2016/9/9
 * Time: 15:25
 */
class FileCache
{
    public $cacheDir;
    public $serializeMethodPair = ['serialize', 'unserialize'];

    /**
     * FileCache constructor.
     * @param string $cacheDir
     * @param array $serializeMethodPair default is `['serialize', 'unserialize']`
     */
     public function __construct($cacheDir, $serializeMethodPair = ['serialize', 'unserialize'])
     {
         if(isset($cacheDir)){
             $this->cacheDir = $cacheDir;
         }
         $this->serializeMethodPair = $serializeMethodPair;
     }

    /**
     * get cache
     * @param string $key
     * @return bool false|mixed
     */
    public function get($key)
    {
        $file = $this->cacheDir . DIRECTORY_SEPARATOR . md5($key);
        if (file_exists($file)) {
            $file_arr = file($file);
            $expires = intval(trim($file_arr[0]));
            if ($expires === 0 || $expires >= time()) {
                return call_user_func($this->serializeMethodPair[1], $file_arr[1]);
            } else {
                unlink($file);
            }
        }
        return false;
    }

    /**
     * set cache
     * @param string $key
     * @param mixed $value
     * @param int $expires
     * @return bool false|int
     */
    public function set($key, $value, $expires = 0)
    {

        if (!$this->checkCacheDir()) {
            return false;
        }

        $file = $this->cacheDir . DIRECTORY_SEPARATOR . md5($key);
        $content = call_user_func($this->serializeMethodPair[0], $value);
        $res = 0;
        if ($expires > 0) {
            $res = file_put_contents($file, ((time() + $expires) . PHP_EOL . $content));
        } else if ($expires == 0) {
            $res = file_put_contents($file, $expires . PHP_EOL . $content);
        }

        return $res;
    }

    /**
     * check cache key exists
     * @param string $key
     * @return bool
     */
    public function keyExists($key)
    {
        $file = $this->cacheDir . DIRECTORY_SEPARATOR . md5($key);
        if (file_exists($file)) {
            $file_arr = file($file);
            $expires = intval(trim($file_arr[0]));
            if ($expires === 0 || $expires >= time()) {
                return true;
            } else {
                unlink($file);
            }
        }
        return false;
    }

    public function remove($key)
    {
        $file = $this->cacheDir . DIRECTORY_SEPARATOR . md5($key);
        if (file_exists($file)) {
            unlink($file);
            return true;
        }
        return false;
    }

    /**
     * flush all cache file
     * @return int
     */
    public function flushAll()
    {
        $count = 0;
        $handle = opendir($this->cacheDir);
        while (($file = readdir($handle)) !== false) {
            if ($file != '.' && $file != '..') {
                if (file_exists($this->cacheDir . DIRECTORY_SEPARATOR . $file)) {
                    @unlink($this->cacheDir . DIRECTORY_SEPARATOR . $file);
                    $count++;
                }
            }
        }
        closedir($handle);
        return $count;
    }

    /**
     * checkCacheDir
     * @return bool
     * @throws Exception
     */
    protected function checkCacheDir()
    {
        if (empty($this->cacheDir)) {
            throw new Exception('Configure cache error', 'cacheDir can not empty');
        }

        if (!file_exists($this->cacheDir) && !mkdir($this->cacheDir)) {
            throw new Exception('Configure cache error', 'make cacheDir failed');
        }

        if (!is_writable($this->cacheDir)) {
            throw new Exception('Configure cache error', 'cacheDir not have write permissions');
        }

        return true;
    }

}