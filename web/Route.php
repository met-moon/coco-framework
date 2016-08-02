<?php
/**
 * Route
 * extend from NoahBuscher\Macaw
 * User: ttt
 * Date: 2016/5/21
 * Time: 23:13
 */

namespace coco\web;

/**
 * @method static Route get(string $route, Callable $callback, string $name = null)
 * @method static Route post(string $route, Callable $callback, string $name = null)
 * @method static Route any(string $route, Callable $callback, string $name = null)
 * @method static Route put(string $route, Callable $callback, string $name = null)
 * @method static Route delete(string $route, Callable $callback, string $name = null)
 * @method static Route options(string $route, Callable $callback, string $name = null)
 * @method static Route head(string $route, Callable $callback, string $name = null)
 */
class Route
{
    protected static $halts = false;

    protected static $origin_routes = array();

    protected static $routes = array();
    protected static $methods = array();
    protected static $callbacks = array();
    protected static $names = array();

    protected static $patterns = array(
        ':any' => '[^/]+',
        ':num' => '[0-9]+',
        ':all' => '.*'
    );

    protected static $error_callback;

    /**
     * @param string $method
     * @param $arguments
     */
    public static function __callStatic($method, $arguments)
    {
        $scriptPathInfo = pathinfo($_SERVER['SCRIPT_NAME']);
        if (strpos($_SERVER['REQUEST_URI'], $scriptPathInfo['basename']) !== false) {
            if ($arguments[0] == '' || $arguments[0] == '/') {
                $uri = $_SERVER['SCRIPT_NAME'];
            } else {
                $uri = rtrim($_SERVER['SCRIPT_NAME'], '/') . '/' . $arguments[0];
            }
        } else {
            $uri = dirname($_SERVER['SCRIPT_NAME']) . '/' . $arguments[0];
            $uri = str_replace('//', '/', $uri);
        }

        $callback = $arguments[1];
        $name = isset($arguments[2]) ? $arguments[2] : uniqid();

        array_push(self::$origin_routes, $arguments[0]);
        array_push(self::$routes, $uri);
        array_push(self::$methods, strtoupper($method));
        array_push(self::$callbacks, $callback);
        array_push(self::$names, $name);
    }

    /**
     * @param Callable $callback
     */
    public static function error($callback)
    {
        self::$error_callback = $callback;
    }

    /**
     * halt on match once
     * @param bool $flag
     */
    public static function haltOnMatch($flag = true)
    {
        self::$halts = $flag;
    }

    public static function dispatch()
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        $method = $_SERVER['REQUEST_METHOD'];

        $searches = array_keys(static::$patterns);
        $replaces = array_values(static::$patterns);

        $found_route = false;

        self::$routes = str_replace('//', '/', self::$routes);

        // Check if route is defined without regex
        if (in_array($uri, self::$routes)) {

            $route_pos = array_keys(self::$routes, $uri);

            foreach ($route_pos as $route) {

                // Using an ANY option to match both GET and POST requests
                if (self::$methods[$route] == $method || self::$methods[$route] == 'ANY') {

                    $found_route = true;

                    // If route is not an object
                    if (!is_object(self::$callbacks[$route])) {

                        // Grab all parts based on a / separator     // why use '/' in callback params ？
                        $parts = explode('/', self::$callbacks[$route]);

                        // Collect the last index of the array
                        $last = end($parts);

                        // Grab the controller name and method call
                        $segments = explode('@', $last);

                        // Instance controller
                        $controller = new $segments[0]();

                        // Call method
                        $controller->{$segments[1]}();

                        if (self::$halts) return;
                    } else {
                        // Call closure
                        call_user_func(self::$callbacks[$route], self::$origin_routes[$route]);

                        if (self::$halts) return;
                    }
                }
            }
        } else {
            // Check if defined with regex
            $pos = 0;
            foreach (self::$routes as $route) {
                if (strpos($route, ':') !== false) {
                    $route = str_replace($searches, $replaces, $route);
                }

                if (preg_match('#^' . $route . '$#', $uri, $matched)) {
                    if (self::$methods[$pos] == $method || self::$methods[$pos] == 'ANY') {
                        $found_route = true;

                        // Remove $matched[0] as [1] is the first parameter.
                        array_shift($matched);

                        if (!is_object(self::$callbacks[$pos])) {

                            // Grab all parts based on a / separator
                            $parts = explode('/', self::$callbacks[$pos]);

                            // Collect the last index of the array
                            $last = end($parts);

                            // Grab the controller name and method call
                            $segments = explode('@', $last);

                            // Instance controller
                            $controller = new $segments[0]();

                            // Fix multi parameters
                            /*if (!method_exists($controller, $segments[1])) {
                                echo "controller and action not found";
                            } else {
                                call_user_func_array(array($controller, $segments[1]), $matched);
                            }*/

                            call_user_func_array(array($controller, $segments[1]), $matched);

                            if (self::$halts) return;
                        } else {
                            call_user_func_array(self::$callbacks[$pos], $matched);

                            if (self::$halts) return;
                        }
                    }
                }
                $pos++;
            }
        }

        // Run the error callback if the route was not found
        if ($found_route == false) {
            if (!self::$error_callback) {
                self::$error_callback = function () {
                    header($_SERVER['SERVER_PROTOCOL'] . " 404 Not Found");
                    echo '404';
                };
            } else {
                if (is_string(self::$error_callback)) {
                    self::get($_SERVER['REQUEST_URI'], self::$error_callback);
                    self::$error_callback = null;
                    self::dispatch();
                    return;
                }
                call_user_func(self::$error_callback, $uri);
            }
        }
    }

    /**
     * get the route by the route's name
     * @param $routeName
     * @return string|bool false
     */
    public static function getRoute($routeName)
    {
        $offset = array_search($routeName, self::$names);
        if ($offset === false) {
            return false;
        }
        return self::$routes[$offset];
    }
}