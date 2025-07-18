<?php

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     3.3.0
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */

declare(strict_types=1);

namespace App;

use Cake\Core\Configure;
use Cake\Core\Exception\MissingPluginException;
use Cake\Error\Middleware\ErrorHandlerMiddleware;
use Cake\Http\BaseApplication;
use Cake\Http\Middleware\BodyParserMiddleware;
use Cake\Http\MiddlewareQueue;
use Cake\Routing\Middleware\AssetMiddleware;
use Cake\Routing\Middleware\RoutingMiddleware;
use Authentication\AuthenticationService;
use Authentication\AuthenticationServiceInterface;
use Authentication\AuthenticationServiceProviderInterface;
use Authentication\Identifier\IdentifierInterface;
use Authentication\Middleware\AuthenticationMiddleware;
use Cake\Routing\Router;
use Cake\Utility\Security;
use Psr\Http\Message\ServerRequestInterface;
use Authorization\AuthorizationService;
use Authorization\AuthorizationServiceInterface;
use Authorization\AuthorizationServiceProviderInterface;
use Authorization\Middleware\AuthorizationMiddleware;
use Authorization\Policy\OrmResolver;
use Psr\Http\Message\ResponseInterface;
use App\Policy\RequestPolicy;
use Authorization\Middleware\RequestAuthorizationMiddleware;
use App\Middleware\EnforceAgreementsAcceptanceMiddleware;
use Authorization\Policy\MapResolver;
use Cake\Http\ServerRequest;
use Cake\Log\Log;

/**
 * Application setup class.
 *
 * This defines the bootstrapping logic and middleware layers you
 * want to use in your application.
 */
class Application extends BaseApplication implements
    AuthenticationServiceProviderInterface,
    AuthorizationServiceProviderInterface
{
    /**
     * Load all the application configuration and bootstrap logic.
     *
     * @return void
     */
    public function bootstrap(): void
    {
        // Call parent to load bootstrap from files.
        parent::bootstrap();

        if (PHP_SAPI === 'cli') {
            $this->bootstrapCli();
        }

        /**
         * Only try to load DebugKit in development mode
         * Debug Kit should not be installed on a production system
         */
        if (Configure::read('debug')) {
            Configure::write('DebugKit.forceEnable', true);
            Configure::write('DebugKit.ignoreAuthorization', true);
            Configure::write('DebugKit.skipRoutes', ['admin']);
            $this->addPlugin('DebugKit', [
                'bootstrap' => true,
                'routes' => true,
                'middleware' => true
            ]);
        }

        /**
         * Plugin to allow Cross-Origin Resource Sharing
         * This is used to handle CORS OPTIONS requests, which check if the
         * requested method is accepted.
         */
        $this->addPlugin('Cors');//, ['bootstrap' => true, 'routes' => false]);

        /**
         * Load CakePHP Authentication plugin
         */
        $this->addPlugin('Authentication');

        /**
         * Load CakePHP Authorization plugin
         */
        $this->addPlugin('Authorization');

        /**
         * Admin theme for backend administrator access
         */
        $this->addPlugin('AdminLTE', ['bootstrap' => true, 'routes' => true]);
    }

    /**
     * Bootrapping for CLI application.
     *
     * That is when running commands.
     *
     * @return void
     */
    protected function bootstrapCli(): void
    {
        try {
            $this->addPlugin('Bake');
        } catch (MissingPluginException $e) {
            // Do not halt if the plugin is missing
        }

        $this->addPlugin('Migrations');

        // Load more plugins here
    }

    /**
     * Setup the middleware queue your application will use.
     *
     * @param MiddlewareQueue $middlewareQueue The middleware queue to setup.
     * @return MiddlewareQueue The updated middleware queue.
     */
    public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
    {
        $middlewareQueue
            // Catch any exceptions in the lower layers,
            // and make an error page/response
            ->add(new ErrorHandlerMiddleware(Configure::read('Error')))

            // Handle plugin/theme assets like CakePHP normally does.
            ->add(new AssetMiddleware([
                'cacheTime' => Configure::read('Asset.cacheTime'),
            ]))

            // Add routing middleware.
            // If you have a large number of routes connected, turning on routes
            // caching in production could improve performance. For that when
            // creating the middleware instance specify the cache config name by
            // using it's second constructor argument:
            // `new RoutingMiddleware($this, '_cake_routes_')`
            ->add(new RoutingMiddleware($this))
            // Parse various types of encoded request bodies so that they are
            // available as array through $request->getData()
            // https://book.cakephp.org/4/en/controllers/middleware.html#body-parser-middleware
            ->add(new BodyParserMiddleware())
            // Add the AuthenticationMiddleware. It should be
            // after routing and body parser.
            ->add(new AuthenticationMiddleware($this))
            // Add the EnforceAgreementsAcceptanceMiddleware *after* routing, body parser
            // and authentication middleware.
            ->add(new EnforceAgreementsAcceptanceMiddleware())
            // Add the AuthorizationMiddleware *after* routing, body parser
            // and authentication middleware.
            ->add(new AuthorizationMiddleware($this, [
                'className' => 'Authorization.Redirect',
                'url' => '/backend/admin/users/login',
                'queryParam' => 'redirectUrl',
                'exceptions' => [
                    \Authorization\Exception\MissingIdentityException::class,
                    \Authorization\Exception\ForbiddenException::class
                ]
            ]))
            // Add Request authorization middleware
            ->add(new RequestAuthorizationMiddleware([
                'unauthorizedHandler' => [
                    'className' => 'Authorization.Redirect',
                    'url' => '/backend/admin/users/login',
                    'queryParam' => 'redirectUrl',
                    'exceptions' => [
                        \Authorization\Exception\MissingIdentityException::class,
                        \Authorization\Exception\ForbiddenException::class
                    ]
                ]
            ]));

        return $middlewareQueue;
    }

    /**
     * Returns a service provider instance.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request Request
     * @return \Authentication\AuthenticationServiceInterface
     */
    public function getAuthenticationService(ServerRequestInterface $request): AuthenticationServiceInterface
    {
        $service = new AuthenticationService();

        $redirectUrl = Router::url([
            'prefix' => 'Admin',
            'controller' => 'Users',
            'action' => 'login',
            'plugin' => null,
        ]);

        // Define where users should be redirected to when they are not authenticated.
        // This queryParam string must be the same as those in
        // the middleware() function above for the unauthorizedHandlers.
        $service->setConfig([
            'unauthenticatedRedirect' => $redirectUrl,
            'queryParam' => 'redirectUrl',
        ]);

        // Load identifiers
        // Email and password fields
        $service->loadIdentifier('Authentication.Password', [
            'fields' => [
                'username' => 'email',
                'password' => 'password'
            ]
        ]);
        // JSON Web Token subject
        $service->loadIdentifier('Authentication.JwtSubject');
        // Load the authenticators. Session should be first.
        // Not using session authentication cause we weren't in CakePHP 3
        // and we would need to do work to implement it correctly at a later date.
        if ($request->getParam('prefix') === 'Admin') {
            $service->loadAuthenticator('Authentication.Session');
        }
        // Configure form data check to pick email and password
        // Need to have all these loginUrls to allow Authentication
        // to work for all these routes.
        $service->loadAuthenticator('Authentication.Form', [
            'fields' => [
                'username' => 'email',
                'password' => 'password'
            ],
            'loginUrl' => [
                Router::url('/'),
                Router::url('/admin'),
                Router::url('/admin/'),
                Router::url('/admin/users/login'),
                Router::url('/admin/users/login/'),
                Router::url('/api/Users/login.json'),
                Router::url('/api/Users/token.json'),
                Router::url('/api/users/login.json'),
                Router::url('/api/users/token.json'),
            ],
        ]);
        // Configure JSON Web Token method
        $service->loadAuthenticator('Authentication.Jwt', [
            'secretKey' => Security::getSalt(),
            'algorithm' => 'HS256',
            'returnPayload' => false
        ]);

        return $service;
    }

    public function getAuthorizationService(ServerRequestInterface $request): AuthorizationServiceInterface
    {
        $resolver = new MapResolver();
        $resolver->map(ServerRequest::class, RequestPolicy::class);

        return new AuthorizationService($resolver);
    }
}
