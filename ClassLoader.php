<?php

/**
 * class autoLoader
 * @author T
 * @date 2015-09-21 12:36
 */
class ClassLoader{

    public static $basePath = __DIR__;	// base dir
    public static $prefixMap = [];		// use customer namespace map
    public static $fileExt = '.php';	// the class file ext name like '.class.php'

    public static function addPrefix($prefix, $path){
        self::$prefixMap["$prefix"] = $path;
    }

    public static function autoload($class){
        foreach (self::$prefixMap as $prefix => $path) {
            $len = strlen($prefix);
            if(strncmp($prefix, $class, $len) !== 0){
                continue;
            }
            if(is_array($path) && !empty($path)){
                foreach ($path as $subPath) {
                    $relativeClass = substr($class, $len);
                    $classFile = self::$basePath.'/'.$subPath.'/'.str_replace('\\', '/', $relativeClass).self::$fileExt;
                    if(file_exists($classFile)){
                        require_once $classFile;
                        return;
                    }
                }
            }else{
                $relativeClass = substr($class, $len);
                $classFile = self::$basePath.'/'.$path.'/'.str_replace('\\', '/', $relativeClass).self::$fileExt;
                if(file_exists($classFile)){
                    require_once $classFile;
                    return;
                }
            }
        }
    }
}