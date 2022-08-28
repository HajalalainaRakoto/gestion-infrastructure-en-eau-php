<?php

namespace App\Controllers;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\PointEau;
use App\Models\Core;
use Slim\Routing\RouteContext;

class PointEauController
{

    private $validationErrors = null;
    private $id_pdo = null;

    public function getForm(Request $request, Response $response)
    {
        $loader = new \Twig\Loader\FilesystemLoader(dirname(dirname(__DIR__)) . '/templates');
        $twig = new \Twig\Environment($loader, [
            'debug' => true,
        ]);
        $twig->addExtension(new \Twig\Extension\DebugExtension());
        $twig->addGlobal('session', $_SESSION);

        $var = new PointEau();
        $core = new Core();
        $type_pdo = $core->distinct('pdo', 'type_pdo');
        $etat_ouvrage = $core->distinct('pdo', 'etat_ouvrage');
        $id_loc = $core->distinct('localite', 'id_loc');
        if (isset($_GET['id_pdo'])) {
            $result = $var->value($_GET['id_pdo']);
            $template = $twig->render('form_point_eau.html.twig', [
            'pdo' => $result['pdo'],
            'errors' => $this->validationErrors,
            'types_pdo' => $type_pdo,
            'etat_ouvrage' => $etat_ouvrage,
            'id_loc' => $id_loc
            ]);
        }

        else {
            $template = $twig->render('form_point_eau.html.twig', [
                'errors' => $this->validationErrors,
                'types_pdo' => $type_pdo,
                'etat_ouvrage' => $etat_ouvrage,
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

        $infra = new PointEau();

        if (isset($_GET['id_pdo'])){
            $rep = $infra->update($newData, $_GET['id_pdo']);

            if ($rep['error']) {
                $this->validationErrors = $rep['error'];
                return $this->getForm($request, $response);
            }
            else {
                $routeParser = RouteContext::fromRequest($request)->getRouteParser();
                $url = $routeParser->urlFor('point-eau');
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
                $url = $routeParser->urlFor('point-eau');
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

        $all = new PointEau();
        $all->delete($_GET['id_pdo']);

        $routeParser = RouteContext::fromRequest($request)->getRouteParser();
        $url = $routeParser->urlFor('point-eau');
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

        $all = new PointEau();

        if (isset($_SESSION['q'])) {
            $recherche = $_SESSION['q'];

            $results = $all->liste(($currentPage - 1) * 300, $recherche);
        } else {
            $results = $all->liste(($currentPage - 1) * 300);
        }
        $template = $twig->render('liste_point_eau.html.twig', [
            'data' => $results['data'],
            'page' => $currentPage,
            'nb' => $results['nb'],
            'all' => $results['all'],
            'start' => $results['start'],
            'end' => $results['end'],
            'search' => $recherche,
            'id_pdo' => $this->id_pdo
        ]);
        $response->getBody()->write($template);
        return $response;
    }

}