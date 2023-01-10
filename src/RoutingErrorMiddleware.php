<?php


namespace Freezemage\MiddlewareSnippet;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;


final class RoutingErrorMiddleware implements MiddlewareInterface
{
    private ResponseFactoryInterface $responseFactory;

    public function __construct(ResponseFactoryInterface $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            $response = $handler->handle($request);
        } catch (HttpMethodNotAllowedException $e) {
            $response = $this->responseFactory->createResponse(405);
            $response->getBody()->write('Method not allowed!');
        } catch (HttpNotFoundException $e) {
            $response = $this->responseFactory->createResponse(404);
            $response->getBody()->write('Page not found!');
        }

        return $response;
    }
}