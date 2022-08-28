<?php

namespace App\Controllers;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\SystemeEau;
use App\Models\Core;
use Slim\Routing\RouteContext;

class SystemeEauController
{

    private $validationErrors = null;

    public function getForm(Request $request, Response $response)
    {
        $loader = new \Twig\Loader\FilesystemLoader(dirname(dirname(__DIR__)) . '/templates');
        $twig = new \Twig\Environment($loader, [
            'debug' => true,
        ]);
        $twig->addExtension(new \Twig\Extension\DebugExtension());
        $twig->addGlobal('session', $_SESSION);

        $var = new SystemeEau();
        $core = new Core();
        $type_infra = $core->distinct('systeme_eau', 'type_infra');
        $id_loc = $core->distinct('localite', 'id_loc');
        if (isset($_GET['id_infra'])) {
            $result = $var->value($_GET['id_infra']);
            $template = $twig->render('form_systeme_eau.html.twig', [
            'systeme_eau' => $result['systeme_eau'],
            'errors' => $this->validationErrors,
            'types_infra' => $type_infra,
            'id_loc' => $id_loc
            ]);
        }

        else {
            $template = $twig->render('form_systeme_eau.html.twig', [
                'errors' => $this->validationErrors,
                'types_infra' => $type_infra,
                'id_loc' => $id_loc
            ]);
       }
        $response->getBody()->write($template);
        return $response;
    }

    public function postForm(Request $request, Response $response)
    {
        $loader = new \Twig\Loader\FilesystemLoader(dirname(dirname(__DIR__)) . '/templates');
        $twig = new \Twig\Environment($loader, [
            'debug' => true,
        ]);
        $twig->addExtension(new \Twig\Extension\DebugExtension());
        $twig->addGlobal('session', $_SESSION);

        $newData =  $request->getParsedBody();
        $infra = new SystemeEau();

        if (isset($_GET['id_infra'])){
            $rep = $infra->update($newData, $_GET['id_infra']);

            if ($rep['error']) {
                $this->validationErrors = $rep['error'];
                return $this->getForm($request, $response);
            }
            else {
                $routeParser = RouteContext::fromRequest($request)->getRouteParser();
                $url = $routeParser->urlFor('systeme-eau');
                $response = $response->withHeader('Location', $url)->withStatus(303);
                return $response;
            }
        }

       else {
            $rep = $infra->insertion($newData);

            if ($rep['error']) {
                $this->validationErrors = $rep['error'];
                return $this->getForm($request, $response);
            }
            else {
                $routeParser = RouteContext::fromRequest($request)->getRouteParser();
                $url = $routeParser->urlFor('systeme-eau');
                $response = $response->withHeader('Location', $url)->withStatus(303);
                return $response;
            }
       }
    }

    public function delete(Request $request, Response $response)
    {
        $loader = new \Twig\Loader\FilesystemLoader(dirname(dirname(__DIR__)) . '/templates');
        $twig = new \Twig\Environment($loader, [
            'debug' => true,
        ]);
        $twig->addExtension(new \Twig\Extension\DebugExtension());
        $twig->addGlobal('session', $_SESSION);

        $all = new SystemeEau();
        $all->delete($_GET['id_infra']);

        $routeParser = RouteContext::fromRequest($request)->getRouteParser();
        $url = $routeParser->urlFor('systeme-eau');
        $response = $response->withHeader('Location', $url)->withStatus(303);
        return $response;
    }

    public function get(Request $request, Response $response) {
        $loader = new \Twig\Loader\FilesystemLoader(dirname(dirname(__DIR__)) . '/templates');
        $twig = new \Twig\Environment($loader, [
            'debug' => true,
        ]);
        $twig->addExtension(new \Twig\Extension\DebugExtension());
        $twig->addGlobal('session', $_SESSION);

            if (!isset($_GET['p'])) {
                $currentPage = 1;
            }
            else {
                $currentPage = $_GET['p'];
            }

            if (!isset($_GET['q'])) {
                $recherche = null;
                unset($_SESSION['q']);
            }
            else {
                $recherche = $_GET['q'];
                $_SESSION['q'] = $_GET['q'];
                $_GET['q'] = $_SESSION['q'];
                $recherche = $_GET['q'];
            }
        $all = new SystemeEau();
        if (isset($_SESSION['q'])) {
            $recherche = $_SESSION['q'];

            $results = $all->liste(($currentPage - 1) * 100, $recherche);
        } else {
            $results = $all->liste(($currentPage - 1) * 100);
        }

        $template = $twig->render('liste_systeme_eau.html.twig', [
            'data' => $results['data'],
            'page' => $currentPage,
            'nb' => $results['nb'],
            'all' => $results['all'],
            'start' => $results['start'],
            'end' => $results['end'],
            'search' => $recherche
        ]);
        $response->getBody()->write($template);
        return $response;
    }

}