<?php


namespace Freezemage\MiddlewareSnippet;


use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;


class AuthorizationMiddlewareTest extends TestCase
{
    /**
     * This is a normal behaviour test.
     * Before calling `Middleware->process()`, we mock all dependencies and all objects that are used within the `process()` method.
     *
     * Basically, it "emulates" all internal calls that happen within `Middleware->process()` method.
     * If anything (e.g. Authorization header name in `$request->getHeaderLine()` call) changes within the original `Middleware->process()` method, then this test will fail.
     *
     * PHPUnit will assert that every mocked call happened exactly expected amount of times (defined by `expects()`) and with expected parameters (defined by `with()`).
     *
     * @return void
     */
    public function testProcess(): void
    {
        $responseFactory = $this->createMock(ResponseFactoryInterface::class);
        // In normal behaviour this never gets called by Middleware.
        $responseFactory->expects($this->never())->method('createResponse');

        $authorizationMiddleware = new AuthorizationMiddleware($responseFactory, 'test', 'test');

        $request = $this->createMock(ServerRequestInterface::class);
        // Middleware ALWAYS calls getHeaderLine to validate credentials, so we expect one call
        $request
                ->expects($this->once())
                ->method('getHeaderLine')
                ->with('Authorization')
                ->willReturn('Basic ' . base64_encode('test:test'));

        // In normal behaviour this gets called once to write authorized user's login in USER attribute, so we expect one call
        $request
                ->expects($this->once())
                ->method('withAttribute')
                ->with('USER', 'test')
                ->willReturn($request);


        // In normal behaviour Middleware calls handler to get response, so we expect one call
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler
                ->expects($this->once())
                ->method('handle')
                ->with($request)
                ->willReturn($this->createMock(ResponseInterface::class));

        // No response asserts required since we're testing Middleware, not RequestHandler
        $authorizationMiddleware->process($request, $handler);
    }

    /**
     * This is "unauthorized user" behaviour test.
     *
     * @return void
     */
    public function testProcess_unauthorized(): void
    {
        $responseFactory = $this->createMock(ResponseFactoryInterface::class);

        $request = $this->createMock(ServerRequestInterface::class);
        // Middleware ALWAYS calls getHeaderLine to validate credentials, so we expect one call
        $request
                ->expects($this->once())
                ->method('getHeaderLine')
                ->with('Authorization')
                ->willReturn('Basic ' . base64_encode('test:invalid-password'));

        // This will be called by middleware to create 401 Unauthorized response
        $response = $this->createMock(ResponseInterface::class);

        $responseFactory
                ->expects($this->once())
                ->method('createResponse')
                ->with(401, 'Unauthorized')
                ->willReturn($response);

        $response
                ->expects($this->once())
                ->method('withHeader')
                ->with('WWW-Authenticate', 'Basic')
                ->willReturn($response);


        // This should never be called since Middleware deemed user as unauthorized
        $request->expects($this->never())->method('withAttribute');

        $handler = $this->createMock(RequestHandlerInterface::class);
        // This should never be called since Middleware deemed user as unauthorized
        $handler->expects($this->never())->method('handle');

        $middleware = new AuthorizationMiddleware($responseFactory, 'test', 'test');
        $middleware->process($request, $handler);
    }
}
