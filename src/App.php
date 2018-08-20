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