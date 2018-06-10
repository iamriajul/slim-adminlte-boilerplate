<?php
/**
 * Created by PhpStorm.
 * User: Riajul
 * Date: 04-Mar-18
 * Time: 9:49 AM
 */

namespace App\Controllers\Auth;


use App\Controllers\Controller;
use Slim\Http\Request;
use Slim\Http\Response;

class LoginController extends Controller
{
    public function showLogin(Request $request, Response $response)
    {
        $data = [];
        $data['title'] = 'Login - ' . $this->appName;
        $data['header'] = 'Login';
        $this->view->render($response, 'login.twig', $data);
    }

    public function login(Request $request, Response $response)
    {

        $username = $request->getParsedBodyParam('username');
        $password = $request->getParsedBodyParam('password');
        $password = md5($password);

        $statement = $this->db->prepare("SELECT * FROM users WHERE user_name = ? AND user_pass = ?");
        $statement->execute([$username, $password]);
        $user_data = $statement->fetch(\PDO::FETCH_ASSOC);

        if ($statement->rowCount() > 0) {

            $_SESSION['user_id'] = $user_data['user_id'];
            $_SESSION['username'] = $user_data['user_name'];

            if (@!empty($_SESSION['redirect']) && $_SESSION['redirect'] != 'login') {

                $redirect = $_SESSION['redirect'];
                unset($_SESSION['redirect']);

                return $response->withRedirect(
                    $this->router->pathFor($redirect, $_SESSION['redirect_args']),
                    302
                );
            }

            return $response->withRedirect(
                $this->router->pathFor('home'),
                302
            );

        } else {
            return $this->view->render($response, 'login.twig', ['error' => true]);
        }

    }

    public function logout(Request $request, Response $response)
    {
        session_destroy();
        return $response->withRedirect(
            $this->router->pathFor('login'),
            302
        );
    }

}