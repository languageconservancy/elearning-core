<?php
declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Authentication\IdentityInterface;
use Cake\Http\Exception\ForbiddenException;
use App\Lib\UtilLibrary;
use Cake\Log\Log;

class EnforceAgreementsAcceptanceMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $identity = $request->getAttribute('identity');
        $prefix = $request->getParam('prefix');
        $controller = $request->getParam('controller');
        $action = $request->getParam('action');

        // List of actions to bypass agreements acceptance enforcement
        $allowedActions = [
            'Api' => [
                'Users' => [
                    'token', 'signup', 'login', 'forgotPassword', 'resetPasswordToken',
                    'checkEmail', 'captchaResponse', 'resetPassword',
                    'contactUs', 'signInWithClever', 'teacherChangePassword',
                ],
                'Sitesetting' => [
                    'fetchLink', 'fetchConstruction', 'fetchCmsContent',
                    'fetchSiteSettingsSettings', 'fetchSiteSettingsFeatures', 'fetchContentByKeyword'
                ],
            ],
            'Admin' => [
                'Users' => [
                    'logout', 'login'
                ]
            ],
        ];

        // Skip check if action is allowed
        if (
            isset($allowedActions[$prefix]) &&
            isset($allowedActions[$prefix][$controller]) &&
            in_array($action, $allowedActions[$prefix][$controller])) {
            return $handler->handle($request);
        }

        // Skip check if not an API request
        if (isset($allowedActions[$prefix]) && $allowedActions[$prefix] !== 'Api') {
            return $handler->handle($request);
        }

        if ($identity instanceof IdentityInterface) {
            $user = $identity->getOriginalData();
            // Check if agreements are not accepted
            if ($prefix === 'Api' && $controller === 'Users' && $action === 'getUser') {
                return $handler->handle($request);
            } else if (!isset($user->agreements_accepted) || $user->agreements_accepted != 1) {
                throw new ForbiddenException(__(UtilLibrary::FORBIDDEN_RESPONSE_REASONS['AGREEMENTS_NOT_ACCEPTED']));
            }
        }

        return $handler->handle($request);
    }
}
