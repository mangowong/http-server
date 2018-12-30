<?php
declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\HttpServer\Router;

/**
 * Class Router
 * @method static addRoute($httpMethod, $route, $handler)
 * @method static addGroup($prefix, callable $callback)
 * @method static get($route, $handler)
 * @method static post($route, $handler)
 * @method static put($route, $handler)
 * @method static delete($route, $handler)
 * @method static patch($route, $handler)
 * @method static head($route, $handler)
 * @package Hyperf\HttpServer\Router
 */
class Router
{
    /**
     * @var string
     */
    protected static $serverName = 'httpServer';

    /**
     * @var DispatcherFactory
     */
    protected static $factory;

    public static function __callStatic($name, $arguments)
    {
        $router = static::$factory->getRouter(static::$serverName);
        return $router->$name(...$arguments);
    }

    public static function addServer($serverName, $callback)
    {
        static::$serverName = $serverName;
        $callback();
        static::$serverName = 'httpServer';
    }

    public static function init(DispatcherFactory $factory)
    {
        static::$factory = $factory;
    }
}
