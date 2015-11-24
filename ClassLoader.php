<?php

/**
 * class autoLoader
 * @author T
 * @date 2015-09-21 12:36
 */
class ClassLoader
{

    /**
     * base directory
     * @var string
     */
    public static $basePath = __DIR__;

    /**
     * use customer namespace prefix map
     * @var array
     */
    public static $prefixMap = [];

    /**
     * class file ext
     * @var string
     */
    public static $fileExt = '.php';

    /**
     * add namespace prefix
     * @param $prefix
     * @param $path
     */
    public static function addPrefix($prefix, $path)
    {
        self::$prefixMap["$prefix"] = $path;
    }

    /**
     * autoload class
     * @param string $class
     * @return bool
     */
    public static function autoload($class)
    {
        foreach (self::$prefixMap as $prefix => $path) {
            $length = strlen($prefix);
            if (strncmp($prefix, $class, $length) !== 0) {
                continue;
            }
            if (is_array($path) && !empty($path)) {
                foreach ($path as $subPath) {
                    self::_requireClassFile($class, $subPath, $length);
                }
            } else {
                self::_requireClassFile($class, $path, $length);
            }
        }
    }

    /**
     * require class file
     * @param string $class
     * @param string $path
     * @param int $length
     * @return bool
     */
    private static function _requireClassFile($class, $path, $length)
    {
        $relativeClass = substr($class, $length);
        $CoCoPath = dirname(__DIR__);
        $classFile = $CoCoPath . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . self::$fileExt;
        if (file_exists($classFile)) {
            require_once $classFile;
            return true;
        } else {
            $classFile = self::$basePath . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . self::$fileExt;
            if (file_exists($classFile)) {
                require_once $classFile;
                return true;
            }
        }
        return false;
    }
}