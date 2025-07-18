<?php

/**
 * Routes configuration
 *
 * In this file, you set up routes to your controllers and their actions.
 * Routes are very important mechanism that allows you to freely connect
 * different URLs to chosen controllers and their actions (functions).
 *
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

declare(strict_types=1);

use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use Cake\Routing\Route\DashedRoute;
use Cake\Log\Log;

return static function (RouteBuilder $routes) {
    /**
     * The default class to use for all routes
     *
     * The following route classes are supplied with CakePHP and are appropriate
     * to set as the default:
     *
     * - Route
     * - InflectedRoute
     * - DashedRoute
     *
     * If no call is made to `Router::defaultRouteClass()`, the class used is
     * `Route` (`Cake\Routing\Route\Route`)
     *
     * Note that `Route` does not do any inflections on URLs which will result in
     * inconsistently cased URLs when used with `:plugin`, `:controller` and
     * `:action` markers.
     */

    // Use dashed routes so that a route like
    //   /my-plugin/my-controller/my-action is parsed as
    //   ['plugin' => 'MyPlugin', 'controller' => 'MyController', 'action' => 'myAction']
    $routes->setRouteClass(DashedRoute::class);

    $routes->scope('/', function (RouteBuilder $builder) {
        // Here, we are connecting '/' (base path) to a controller called 'Users',
        // its action called 'login', prefixed with 'admin'
        $builder->connect('/', ['controller' => 'Users', 'action' => 'login', 'prefix' => 'Admin']);
        /*
         * Connect catchall routes for all controllers.
         *
         * The `fallbacks` method is a shortcut for
         *
         * ```
         * $builder->connect('/{controller}', ['action' => 'index']);
         * $builder->connect('/{controller}/{action}/*', []);
         * ```
         * Using the argument `DashedRoute`, the `fallbacks` method is a shortcut for
         *    `$routes->connect('/{controller}', ['action' => 'index'], ['routeClass' => 'DashedRoute']);`
         *    `$routes->connect('/{controller}/{action}/*', [], ['routeClass' => 'DashedRoute']);`
         *
         * Any route class can be used with this method, such as:
         * - DashedRoute
         * - InflectedRoute
         * - Route
         * - Or your own route class
         *
         * You can remove these routes once you've connected the
         * routes you want in your application.
         */
        $builder->fallbacks(DashedRoute::class);
    });

    // Prefix routes ending in .json with 'api/'.
    // Connect index path of '/' to Users controller's login action
    $routes->prefix('api', function (RouteBuilder $builder) {
        $builder->setExtensions(['json']);
        $builder->connect(
            '/',
            ['controller' => 'Users', 'action' => 'login']
        );
        $builder->connect('/download/:filename',
            ['controller' => 'Templates', 'action' => 'download']
        )->setPass(['filename'])->setExtensions([]);
        $builder->fallbacks(DashedRoute::class);
    });

    // All routes here will be prefixed with '/admin'
    $routes->prefix('admin', function (RouteBuilder $builder) {
        $builder->connect('/', ['controller' => 'Users', 'action' => 'login']);
        $builder->connect('/{controller}/{action}/*', [], ['routeClass' => 'DashedRoute']);
        $builder->fallbacks(DashedRoute::class);
    });

    $routes->prefix('social', function (RouteBuilder $builder) {
        $builder->connect('/{route}', ['controller' => 'social', 'action' => 'index'], ['pass' => ['route']]);
        $builder->fallbacks(DashedRoute::class);
    });

    // Define routes for the DebugKit plugin
    $routes->plugin('DebugKit', ['path' => '/debug-kit'], function (RouteBuilder $builder) {
        $builder->connect('/:controller');
        $builder->connect('/:controller/:action/*');
    });
};
