<?php

/**
 * Psr4 class autoLoader
 * @author T
 * @date 2015-09-21 12:36
 * @last_update_date 2016-03-20
 */
class ClassLoader
{
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
     * @param string $prefix
     * @param string $base_dir
     */
    public static function addPrefix($prefix, $base_dir)
    {
        $prefix = trim($prefix, '\\') . '\\';
        if (is_array($base_dir)) {
            if (!empty($base_dir)) {
                foreach ($base_dir as &$each_dir) {
                    $each_dir = rtrim($each_dir, '/\\') . DIRECTORY_SEPARATOR;
                }
            }
        } else {
            $base_dir = rtrim($base_dir, '/\\') . DIRECTORY_SEPARATOR;
        }

        self::$prefixMap["$prefix"] = $base_dir;
    }

    /**
     * autoload class
     * @param string $class
     * @return bool
     */
    public static function autoload($class)
    {
        $required = false;
        foreach (self::$prefixMap as $prefix => $path) {
            $length = strlen($prefix);
            if (strncmp($prefix, $class, $length) !== 0) {
                continue;
            }
            $relativeClass = substr($class, $length);
            if (is_array($path)) {
                if (!empty($path)) {
                    foreach ($path as $subPath) {
                        $classFile = $subPath . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . self::$fileExt;
                        $required = self::requireFile($classFile);
                        if ($required) {
                            break;
                        }
                    }
                }
            } else {
                $classFile = $path . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . self::$fileExt;
                $required = self::requireFile($classFile);
            }
            if ($required) {
                break;
            }
        }

        return $required;
    }

    /**
     * require file
     * @param string $file
     * @return bool
     */
    protected static function requireFile($file)
    {
        if (file_exists($file)) {
            require_once $file;
            return true;
        }
        return false;
    }
}