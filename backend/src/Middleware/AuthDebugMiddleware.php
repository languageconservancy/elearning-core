<?php
declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Fix Authorization header for Apache/FastCGI compatibility.
 *
 * Some Apache/FastCGI configurations (like MAMP 7) don't properly pass
 * the Authorization header to CakePHP's PSR-7 request object, even though
 * it's available in $_SERVER. This middleware ensures the header is set.
 */
class AuthDebugMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Check if Authorization header is missing from request but present in $_SERVER
        if (empty($request->getHeaderLine('Authorization'))) {
            $serverAuth = $_SERVER['HTTP_AUTHORIZATION']
                ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
                ?? null;

            if ($serverAuth) {
                // Add the Authorization header to the request
                $request = $request->withHeader('Authorization', $serverAuth);
            }
        }

        return $handler->handle($request);
    }
}
