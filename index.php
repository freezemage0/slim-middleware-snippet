<?php


use Freezemage\MiddlewareSnippet\AuthorizationMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Factory\AppFactory;


require __DIR__ . '/vendor/autoload.php';

$slim = AppFactory::create();

$auth = new AuthorizationMiddleware($slim->getResponseFactory(), 'freezemage0', '123');
$slim->get('/', function (ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
    $response->getBody()->write('Hello, World!');
    return $response;
});

$slim->get('/admin', function (ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
    $response->getBody()->write('Hello, Admin!');
    return $response;
})->add($auth);

$slim->run();