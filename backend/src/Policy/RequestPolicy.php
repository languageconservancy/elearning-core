<?php

namespace App\Policy;

use Authorization\Policy\RequestPolicyInterface;
use Cake\Http\ServerRequest;
use Authorization\IdentifierInterface;
use App\Lib\UtilLibrary;
use Cake\ORM\TableRegistry;
use Cake\Log\Log;

class RequestPolicy implements RequestPolicyInterface
{
    /**
     * Method to check if the request can be accessed
     *
     * @param \Authorization\IdentityInterface|null $identity Identity
     * @param \Cake\Http\ServerRequest $request Server Request
     * @return bool
     */
    public function canAccess($identity, ServerRequest $request)
    {
        $allowed = include CONFIG . 'allowed_routes.php';

        $prefix = $request->getParam('prefix');
        $controller = $request->getParam('controller');
        $action = $request->getParam('action');
        $plugin = $request->getParam('plugin');

        // Allow DebugKit plugin routes
        if ($plugin === 'DebugKit') {
            return true;
        }

        // Allow allowed prefixes
        if (!empty($prefix) && in_array($prefix, $allowed['Prefixes'])) {
            return true;
        }
        // Allow allowed controllers
        if (
            !empty($controller)
            && in_array($controller, $allowed['Controllers'])
        ) {
            return true;
        }
        // Allow allowed controller-action pairs
        if (
            !empty($allowed['ControllerActions'][$controller])
            && in_array($action, $allowed['ControllerActions'][$controller])
        ) {
            return true;
        }

        // If not Api, Admin or DebugKit requests, deny access
        if (
            $prefix !== 'Admin' &&
            $controller !== 'Img' &&
            $plugin !== 'DebugKit'
        ) {
            return false;
        }

        if (empty($identity)) {
            return false;
        }

        // User should be active and have a platform role of superadmin
        // to go to access any other Admin action besides login.
        $user = $identity->getOriginalData();

        // Alert if we don't have a user identity object
        if (empty($user)) {
            return false;
        }

        // If user is active, not deleted, and is a superadmin or content developer, let the request pass
        $rolesTable = TableRegistry::getTableLocator()->get('Roles');
        $privilegedRoleIds = $rolesTable->getRoleIdsThatCanAccessAllPaths();
        if (
            $user->is_active === "1" &&
            $user->is_delete === "0" &&
            in_array($user->role_id, $privilegedRoleIds)
        ) {
            return true;
        }

        Log::warning(($user->email ?? "User")
            . " didn't pass any request authorization policy checks");
        return false;
    }
}
