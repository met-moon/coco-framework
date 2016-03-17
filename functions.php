<?php
/**
 * functions
 * User: ttt
 * Date: 2016/3/6
 * Time: 17:58
 */

/**
 * echo json_encode data and exit
 * @param array $data
 */
function echoJson($data)
{
    header('Content-type:application/json');
    echo json_encode($data);
    exit;
}

/**
 * pretty var_dump()
 * @param $var
 */
function dump($var)
{
    $argc = func_num_args();

    if ($argc > 1) {
        echo '<pre>';
        for ($i = 0; $i < $argc; $i++) {
            var_dump(func_get_arg($i));
        }
        echo '</pre>';
    } else {
        echo '<pre>';
        var_dump($var);
        echo '</pre>';
    }
}

/**
 * Check if it is an associative array
 * @param array $array
 * @return bool
 */
function is_assoc($array)
{
    return array_keys($array) !== range(0, count($array) - 1);
}