<?php

namespace App\Controllers;

use App\Models\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Routing\RouteContext;

class UserController
{

    private $error;
    private $errorFile;
    private $errorPass;
    private $errorName;
    private $errorValidationCode;
    private $validationErrors;
    private $validPass;

    public function goToRegister(Request $request, Response $response)
    {
        $loader = new \Twig\Loader\FilesystemLoader(dirname(dirname(__DIR__)) . '/templates');
        $twig = new \Twig\Environment($loader, [
            'debug' => true
        ]);
        $twig->addExtension(new \Twig\Extension\DebugExtension());
        $twig->addGlobal('session', $_SESSION);

        $response->getBody()->write($twig->render('user/register_form.html.twig', [
            'errors' => $this->validationErrors,
            'errorName' => $this->errorName,
            'errorValidationCode' => $this->errorValidationCode
        ]));
        return $response;

    }

    public function register(Request $request, Response $response)
    {
        $userEntity = new User();
        $credentials = $request->getParsedBody();
        $user = $userEntity->findBy($credentials['username']);

        if ($user) {
            $this->errorName = 'Username already in use';
            return $this->goToRegister($request, $response);
        }


        if ($credentials['code'] != $_ENV['CODE_VALIDATION'] ) {

            $this->errorValidationCode = 'Validation code error';
            return $this->goToRegister($request, $response);
        }

        $donneeUser = $userEntity->create($credentials);

        if ($donneeUser['error']) {
            $this->validationErrors = $donneeUser['error'];
            return $this->goToRegister($request, $response);
        }

        $user = $userEntity->findBy($credentials['username']);
        $_SESSION['user'] = [
            'username' => $user['username'],
            'password' => $user['password'],
            'user_id' => $user['user_id'],
            'avatar' =>  $user['avatar']
        ];
        // Redirect to home route
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();
        $url = $routeParser->urlFor('home');
        $response = $response->withHeader('Location', $url)->withStatus(303);
        return $response;
    }

    public function goToLogin(Request $request, Response $response)
    {
        $loader = new \Twig\Loader\FilesystemLoader(dirname(dirname(__DIR__)) . '/templates');
        $twig = new \Twig\Environment($loader, [
            'debug' => true
        ]);
        $twig->addExtension(new \Twig\Extension\DebugExtension());
        $twig->addGlobal('session', $_SESSION);

        $response->getBody()->write($twig->render('user/login_form.html.twig', [
            'errorPass' => $this->errorPass,
            'errorName' => $this->errorName
        ]));
        return $response;
    }

    public function login(Request $request, Response $response)
    {
        $credentials = $request->getParsedBody();
        $userEntity = new User();
        $user = $userEntity->findBy($credentials['username']);
        if (!$user) {
            $this->errorName = 'Username not found';
            return $this->goToLogin($request, $response);
        }

        if (hash_equals(hash('sha256', $credentials['password']), $user['password'])) {
            $_SESSION['user'] = [
                'username' => $user['username'],
                'password' => $user['password'],
                'user_id' => $user['user_id'],
                'avatar' =>  $user['avatar']
            ];
            // Redirect to home route
            $routeParser = RouteContext::fromRequest($request)->getRouteParser();
            $url = $routeParser->urlFor('home');
            $response = $response->withHeader('Location', $url)->withStatus(303);
            return $response;
        } else {
            $this->errorPass = 'Wrong password';
            return $this->goToLogin($request, $response);
        }

    }

    public function logout(Request $request, Response $response)
    {
        session_destroy();
        // Redirect to login route
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();
        $url = $routeParser->urlFor('login');
        $response = $response->withHeader('Location', $url)->withStatus(303);
        return $response;

    }

    public function setting(Request $request, Response $response) {
        $loader = new \Twig\Loader\FilesystemLoader(dirname(dirname(__DIR__)) . '/templates');
        $twig = new \Twig\Environment($loader, [
            'debug' => true,
        ]);
        $twig->addExtension(new \Twig\Extension\DebugExtension());
        $twig->addGlobal('session', $_SESSION);

        $template = $twig->render('profile_setting.html.twig',[
            'error' => $this->error,
            'errorFile' => $this->errorFile,
            'errorPass' => $this->errorPass,
            'validPass' => $this->validPass
        ]);
        $response->getBody()->write($template);
        return $response;
    }

    public function postSetting(Request $request, Response $response) {
        $loader = new \Twig\Loader\FilesystemLoader(dirname(dirname(__DIR__)) . '/templates');
        $twig = new \Twig\Environment($loader, [
            'debug' => true,
        ]);
        $twig->addExtension(new \Twig\Extension\DebugExtension());
        $twig->addGlobal('session', $_SESSION);

        $post = $request->getParsedBody();
        $user = new User();
        if (isset($post['username'])) {
            $updateUsername = $user->updateUsername($post);
            if ($updateUsername['error']) {
                $this->error = $updateUsername['error'];
                return $this->setting($request, $response);
            }
        }

        if (isset($post['old_password']) AND isset($post['new_password']) AND isset($post['confirm_password'])) {
            $updatePwd = $user->updatePwd($post);
            if (isset($updatePwd['errorPass'])) {
                $this->errorPass = $updatePwd['errorPass'];
                return $this->setting($request, $response);
            }
            elseif (isset($updatePwd['error'])) {
                $this->error = $updatePwd['error'];
                return $this->setting($request, $response);
            }else {
                $this->validPass = $updatePwd['validPass'];
                return $this->setting($request, $response);
            }
        }


        if (isset($_FILES['avatar']) AND $_FILES['avatar']['error'] == 0)
        {
            $basename = bin2hex(random_bytes(8));
            $infosfichier = pathinfo($_FILES['avatar']['name']);
            $extension_upload = $infosfichier['extension'];
            $extensions_autorisees = array('jpg', 'jpeg', 'gif', 'png');
            if (in_array($extension_upload, $extensions_autorisees))
            {
                move_uploaded_file($_FILES['avatar']['tmp_name'], dirname(dirname(__DIR__)). DIRECTORY_SEPARATOR . 'public' .DIRECTORY_SEPARATOR. 'img' .DIRECTORY_SEPARATOR . $basename . '.' .pathinfo($_FILES['avatar']['name'])['extension']);
                $user->updateAvatar('\img'. DIRECTORY_SEPARATOR . $basename . '.' .pathinfo($_FILES['avatar']['name'])['extension']);
            }else {
                $this->errorFile = "Fichier Non Prise En Charge";
                return $this->setting($request, $response);
            }
        }

        $routeParser = RouteContext::fromRequest($request)->getRouteParser();
        $url = $routeParser->urlFor('profile');
        $response = $response->withHeader('Location', $url)->withStatus(303);
        return $response;
    }

    public function profile(Request $request, Response $response) {
        $loader = new \Twig\Loader\FilesystemLoader(dirname(dirname(__DIR__)) . '/templates');
        $twig = new \Twig\Environment($loader, [
            'debug' => true,
        ]);
        $twig->addExtension(new \Twig\Extension\DebugExtension());
        $twig->addGlobal('session', $_SESSION);

        $template = $twig->render('profile.html.twig');
        $response->getBody()->write($template);
        return $response;
    }

    public function deleteProfile(Request $request, Response $response) {
        $loader = new \Twig\Loader\FilesystemLoader(dirname(dirname(__DIR__)) . '/templates');
        $twig = new \Twig\Environment($loader, [
            'debug' => true,
        ]);
        $twig->addExtension(new \Twig\Extension\DebugExtension());
        $twig->addGlobal('session', $_SESSION);
        $user = new User();
        $user->delete();
        session_destroy();

        $routeParser = RouteContext::fromRequest($request)->getRouteParser();
        $url = $routeParser->urlFor('login');
        $response = $response->withHeader('Location', $url)->withStatus(303);
        return $response;

    }

}