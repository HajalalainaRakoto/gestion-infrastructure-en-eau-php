<?php
/**
 * Created by PhpStorm.
 * User: smart
 * Date: 6/2/21
 * Time: 6:24 PM
 */

namespace App\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Routing\RouteContext;


/**
 * Auth Middleware.
 */
final class AuthMiddleware implements MiddlewareInterface
{
    /**
     * Invoke middleware.
     *
     * @param ServerRequestInterface $request The request
     * @param RequestHandlerInterface $handler The handler
     *
     * @return ResponseInterface The response
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $isLoggedIn = isset($_SESSION['user']); // check user login / session here

        if ($isLoggedIn) {
            return $handler->handle($request);
        }

        // Redirect to login route
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();
        $url = $routeParser->urlFor('login');

        $response = $handler->handle($request);

        $response = $response->withHeader('Location', $url)->withStatus(303);
        return $response;

    }
}