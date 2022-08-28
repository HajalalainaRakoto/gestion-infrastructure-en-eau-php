<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\Core;

class CoreController
{

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
        } else {
            $currentPage = $_GET['p'];
        }

        if (!isset($_GET['q'])) {
            $recherche = null;
            unset($_SESSION['q']);
        } else {
            $recherche = $_GET['q'];
            $_SESSION['q'] = $_GET['q'];
            $_GET['q'] = $_SESSION['q'];
            $recherche = $_GET['q'];
        }

        $all = new Core();

        if (isset($_SESSION['q'])) {
            $recherche = $_SESSION['q'];

            $results = $all->liste(($currentPage - 1) * 300, $recherche);
        } else {
            $results = $all->liste(($currentPage - 1) * 300);
        }

        $template = $twig->render('liste_infra.html.twig', [
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

    public function getChart(Request $request, Response $response)
    {
        $loader = new \Twig\Loader\FilesystemLoader(dirname(dirname(__DIR__)) . '/templates');
        $twig = new \Twig\Environment($loader, [
            'debug' => true,
        ]);
        $twig->addExtension(new \Twig\Extension\DebugExtension());
        $twig->addGlobal('session', $_SESSION);

        $all = new Core();
        $compter = $all->chart();
        $year = $all->year();

        if (isset($_GET['year'])) {
            $annee = $all->taux($_GET['year']);
            $template = $twig->render('chart.html.twig', [
                'donnee' => $compter,
                'year' => $year,
                'annee' => $annee
            ]);
        } else {
            $annee = $all->taux(2017);
            $template = $twig->render('chart.html.twig', [
                'donnee' => $compter,
                'year' => $year,
                'annee' => $annee
            ]);
        }

        $response->getBody()->write($template);
        return $response;
    }

    public function getDetails(Request $request, Response $response)
    {
        $loader = new \Twig\Loader\FilesystemLoader(dirname(dirname(__DIR__)) . '/templates');
        $twig = new \Twig\Environment($loader, [
            'debug' => true,
        ]);
        $twig->addExtension(new \Twig\Extension\DebugExtension());
        $twig->addGlobal('session', $_SESSION);

        $all = new Core();
        $details = $all->details($_GET['id_loc'], $_GET['id_pdo'], $_GET['id_infra']);

        $template = $twig->render('details.html.twig', [
            'donnee' => $details
        ]);
        $response->getBody()->write($template);
        return $response;
    }
}
