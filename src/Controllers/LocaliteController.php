<?php

namespace App\Controllers;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\Localite;
use Slim\Routing\RouteContext;

class LocaliteController
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

        $var = new Localite();
        if (isset($_GET['id_loc'])) {
            $result = $var->value($_GET['id_loc']);
            $template = $twig->render('form_localite.html.twig', [
            'localite' => $result['localite'],
            'errors' => $this->validationErrors
            ]);
        }

        else {
            $template = $twig->render('form_localite.html.twig', [
                'errors' => $this->validationErrors
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

        $infra = new Localite();

        if (isset($_GET['id_loc'])){
            $rep = $infra->update($newData, $_GET['id_loc']);

            if ($rep['error']) {
                $this->validationErrors = $rep['error'];
                return $this->getForm($request, $response);
            }
            else {
                $routeParser = RouteContext::fromRequest($request)->getRouteParser();
                $url = $routeParser->urlFor('localite');
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
                $url = $routeParser->urlFor('localite');
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

        $all = new Localite();
        $all->delete($_GET['id_loc']);

        $routeParser = RouteContext::fromRequest($request)->getRouteParser();
        $url = $routeParser->urlFor('localite');
        $response = $response->withHeader('Location', $url)->withStatus(303);
        return $response;
    }

    public function get(Request $request, Response $response)
    {
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
        $all = new Localite();

        if (isset($_SESSION['q'])) {
            $recherche = $_SESSION['q'];

            $results = $all->liste(($currentPage - 1) * 350, $recherche);
        } else {
            $results = $all->liste(($currentPage - 1) * 350);
        }

        $template = $twig->render('liste_localite.html.twig', [
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
