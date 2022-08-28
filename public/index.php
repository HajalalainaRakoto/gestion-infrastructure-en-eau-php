<?php
require('../vendor/autoload.php');

if (empty($_SESSION)) {
    session_start();
}

use Dotenv\Dotenv;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteContext;
use App\Controllers\UserController;

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$whoops = new \Whoops\Run;
$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
$whoops->register();

$responseFactory = new \Slim\Psr7\Factory\ResponseFactory();
$app = new  \Slim\App($responseFactory);

// Add error middleware
$app->addErrorMiddleware(true, true, true);

// Routes without authentication check
$app->group('/users', function () use ($app) {
    // Add routes
    $app->get('/register', UserController::class . ':goToRegister')->setName("register");
    $app->get('/login', UserController::class . ':goToLogin')->setName("login");
    $app->post('/register', UserController::class . ':register');
    $app->post('/login', UserController::class . ':login');

})->add(\App\Middlewares\IsConnectedMiddleware::class);

// Routes with authentication
$app->group('', function () use ($app) {
    // Default page
    $app->get('/', function (Request $request, Response $response) {
        // Redirect to home route
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();
        $url = $routeParser->urlFor('home');
        $response = $response->withHeader('Location', $url)->withStatus(303);
        return $response;
    });

    $app->get('/profile-setting', App\Controllers\UserController::class . ':setting')->setName('setting');
    $app->post('/profile-setting', App\Controllers\UserController::class . ':postSetting');
    $app->get('/profile', App\Controllers\UserController::class . ':profile')->setName('profile');
    $app->get('/delete-profile', App\Controllers\UserController::class . ':deleteProfile');
    $app->get('/logout', UserController::class . ':logout');

    $app->get('/chart', App\Controllers\CoreController::class . ':getChart')->setName('home');
    $app->get('/liste-infra', App\Controllers\CoreController::class . ':get');
    $app->get('/details', App\Controllers\CoreController::class . ':getDetails');

    $app->get('/form-localite', App\Controllers\LocaliteController::class . ':getForm');
    $app->get('/liste-localite', App\Controllers\LocaliteController::class . ':get')->setName('localite');
    $app->get('/delete-localite', App\Controllers\LocaliteController::class . ':delete');
    $app->post('/form-localite', App\Controllers\LocaliteController::class . ':postForm');

    $app->get('/liste-point-eau', App\Controllers\PointEauController::class . ':get')->setName('point-eau');
    $app->post('/liste-point-eau', App\Controllers\PointEauController::class . ':get');
    $app->get('/delete-point-eau', App\Controllers\PointEauController::class . ':delete');
    $app->get('/form-point-eau', App\Controllers\PointEauController::class . ':getForm');
    $app->post('/form-point-eau', App\Controllers\PointEauController::class . ':postForm');

    $app->get('/form-systeme-eau', App\Controllers\SystemeEauController::class . ':getForm');
    $app->get('/liste-systeme-eau', App\Controllers\SystemeEauController::class . ':get')->setName('systeme-eau');
    $app->get('/delete-systeme-eau', App\Controllers\SystemeEauController::class . ':delete');
    $app->post('/form-systeme-eau', App\Controllers\SystemeEauController::class . ':postForm');

})->add(\App\Middlewares\AuthMiddleware::class);

$app->run();