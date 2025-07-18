<?php

namespace App\Middleware;

use Cake\Routing\RoutingApplicationInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ForcePostRequestMiddleware implements MiddlewareInterface
{
    private RoutingApplicationInterface $app;

    public function __construct(RoutingApplicationInterface $app)
    {
        $this->app = $app;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
//      // TODO: Implement process() method.
//      if (!$request->is('post')) {
//          throw new Exception
//      }
//
//          $middleware = new MiddlewareQueue($matching);
//      $runner = new Runner();
//
//      return $runner->run($middleware, $request, $handler);
    }
}
