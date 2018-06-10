<?php
/**
 * Created by PhpStorm.
 * User: Riajul
 * Date: 02-Mar-18
 * Time: 3:21 PM
 */

namespace App\Middleware;

use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Slim\Router;

/**
 * Class Middleware
 * @package App\Middleware
 *
 * Properties you can have access:
 * @property Logger $logger
 * @property \PDO $db
 * @property Router $router
 *
 */
abstract class Middleware
{

    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __get($name)
    {
        if (@$this->container->{$name}) {
            return $this->container->{$name};
        }
        return $this->{$name};
    }

}