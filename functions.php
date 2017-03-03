<?php
/**
 * functions
 * User: ttt
 * Date: 2016/3/6
 * Time: 17:58
 */

if (!function_exists('dump')) {
    /**
     * pretty var_dump()
     * @param mixed $var
     */
    function dump($var)
    {
        echo '<pre>';
        foreach (func_get_args() as $var) {
            var_dump($var);
        }
        echo '</pre>';
    }
}

if (!function_exists('is_assoc')) {
    /**
     * Check if it is an associative array
     * @param array $array
     * @return bool
     */
    function is_assoc(array $array)
    {
        return array_keys($array) !== range(0, count($array) - 1);
    }
}
