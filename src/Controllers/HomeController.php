<?php
/**
 * Created by PhpStorm.
 * User: Riajul
 * Date: 04-Mar-18
 * Time: 9:35 AM
 */

namespace App\Controllers;


use Slim\Http\Request;
use Slim\Http\Response;

class HomeController extends Controller
{
    public function home(Request $request, Response $response)
    {
        $data = [];
        $data['route'] = $request->getAttribute('route')->getName();
        $data['title'] = 'Home - ' . $this->appName;
        $data['header'] = 'Home';
        $data['description'] = 'You can see all things at once here!';

        $this->view->render($response, 'home.twig', $data);
    }
}