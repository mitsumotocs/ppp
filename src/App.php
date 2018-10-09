<?php
namespace PPP;

use PPP\Config\Config;

class App
{
    /** @var Config $config */
    public static $config;

    /** @var array $routes */
    public static $routes = [];

    /** @var \Closure $error */
    public static $error;

    /**
     * @param Config $config
     * @return void
     */
    public static function configure(Config $config)
    {
        static::$config = $config;
    }

    /**
     * @param string|null $method
     * @param string $pattern
     * @param \Closure $callback
     * @return void
     */
    public static function route($method = null, $pattern, \Closure $callback)
    {
        $route = [
            'method' => is_string($method) ? strtoupper($method) : null,
            'pattern' => $pattern,
            'callback' => $callback
        ];
        array_unshift(static::$routes, $route);
    }

    /**
     * @param Config|null $config
     * @return mixed
     */
    public static function run(Config $config = null)
    {
        // prepare config
        if (isset($config)) {
            static::configure($config);
        }
        if (!(static::$config instanceof Config)) {
            static::$config = new Config;
        }

        // process request method and URL (to "path")
        $method = strtoupper($_SERVER['REQUEST_METHOD']);
        $path = trim(preg_replace(
            sprintf('/^%s/', preg_quote(dirname($_SERVER['SCRIPT_NAME']), '/')),
            '',
            preg_replace('/\?.*$/', '', urldecode($_SERVER['REQUEST_URI']))
        ), '/');

        // traverse routes
        $callback = null;
        $params = null;
        foreach (static::$routes as $i => $route) {
            if (@preg_match($route['pattern'], $path, $matches) === 1) {
                if (is_null($route['method']) || $route['method'] === $method) {
                    // method and pattern both matched
                    $callback = $route['callback'];
                    $params = array_slice($matches, 1);
                    break;
                } else {
                    // pattern matched, but method did not
                }
            } else {
                // pattern did not match
            }
        }

        // call the callback
        try {
            if (is_callable($callback)) {
                return call_user_func_array($callback, $params);
            } else {
                throw new \RuntimeException('Not Found', 404);
            }
        } catch (\Exception $e) {
            if (is_callable(static::$error)) {
                return call_user_func(static::$error, $e);
            } else {
                http_response_code($e->getCode());
                // TODO: need a fancier display
                die(sprintf('%s: %s', $e->getCode(), $e->getMessage()));
            }
        }
    }

    /**
     * @param $url
     * @param int|null $code
     */
    public static function redirect($url, $code = null)
    {
        http_response_code(isset($code) ? intval($code) : 302);
        header('Location: ' . $url);
        exit;
    }
}