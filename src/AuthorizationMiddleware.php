<?php


namespace Freezemage\MiddlewareSnippet;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;


final class AuthorizationMiddleware implements MiddlewareInterface
{
    private ResponseFactoryInterface $responseFactory;
    private string $login;
    private string $password;

    public function __construct(ResponseFactoryInterface $responseFactory, string $login, string $password)
    {
        $this->responseFactory = $responseFactory;
        $this->login = $login;
        $this->password = $password;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $credentials = base64_encode("{$this->login}:{$this->password}");
        if ($request->getHeaderLine('Authorization') != "Basic {$credentials}") {
            return $this->responseFactory
                    ->createResponse(401, 'Unauthorized')
                    ->withHeader('WWW-Authenticate', 'Basic');
        }

        $request = $request->withAttribute('USER', $this->login);
        return $handler->handle($request);
    }
}