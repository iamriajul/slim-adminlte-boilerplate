<?php
/**
 * Created by PhpStorm.
 * User: Riajul
 * Date: 04-Mar-18
 * Time: 11:01 AM
 */

namespace App\Middleware;


use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Route;

class LoginRequiredMiddleware extends Middleware
{
    public function __invoke(Request $request, Response $response, $next)
    {
        /**
         * @var Route $route
         */
        $route = $request->getAttribute('route');

        if (@!filter_var($_SESSION['user_id'], FILTER_VALIDATE_INT) || empty($_SESSION['username'])) {
            if ($route->getName()) {
                $_SESSION['redirect'] = $route->getName();
                $_SESSION['redirect_args'] = $route->getArguments();
            }
            return $response->withRedirect(
                $this->router->pathFor('login'),
                302
            );
        }

        $response = $next($request, $response);

        return $response;
    }

}