<?php
/**
 * Created by PhpStorm.
 * User: Riajul
 * Date: 04-Mar-18
 * Time: 11:56 AM
 */

namespace App\Middleware;


use Slim\Http\Request;
use Slim\Http\Response;

class NonLoginRequiredMiddleware extends Middleware
{
    public function __invoke(Request $request, Response $response, $next)
    {
        if (@filter_var($_SESSION['user_id'], FILTER_VALIDATE_INT) && !empty($_SESSION['username'])) {
            return $response->withRedirect(
                $this->router->pathFor('home'),
                304
            );
        }

        $response = $next($request, $response);

        return $response;
    }

}