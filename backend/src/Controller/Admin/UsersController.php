<?php

namespace App\Controller\Admin;

use Cake\Cache\Cache;
use Cake\Event\EventInterface;
use Cake\Http\Response;
use App\Lib\UtilLibrary;
use Cake\Log\Log;

class UsersController extends AppController
{
    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->Authentication->allowUnauthenticated([
            'logout', 'login'
        ]);
        $this->loadComponent('Mail');

        // Define actions restricted to super admin
        $restrictedActions = [
            'userList', 'addUsers', 'edit', 'delete', 'status', 'updateUserData',
            'bulkAction', 'addSchoolUser', 'editSchoolUser', 'deleteSchoolUser'
        ];
        $action = $this->request->getParam('action');

        // Only apply restriction if the current action is restricted
        if (in_array($action, $restrictedActions)) {
            $user = $this->getAuthUser();

            // Check if the user is not a super admin
            if ($user->role_id !== $this->getRolesTable()->getRoleId(UtilLibrary::ROLE_SUPERADMIN_STR)) {
                $this->Flash->error(__('You must be an admin to access this resource.'));
                return $this->redirect('/admin/users/dashboard');
            }
        }
    }

    //for fet fetch the user list
    public function userList()
    {
        $condition = array('Users.is_delete' => 0);
        if (isset($_GET['q']) && $_GET['q'] != null) {
            $condition['Users.name LIKE'] = '%' . $_GET['q'] . '%';
        }

        $query = $this->getUsersTable()->find()
            ->where($condition)
            ->contain(['Roles']);

        $this->set('users', $this->paginate($query));
        $this->viewBuilder()->setOption('serialize', ['users']);
    }

    //for add the user
    public function addUsers()
    {
        $user = $this->getUsersTable()->newEmptyEntity();
        if ($this->request->is('post')) {
            $user = $this->getUsersTable()->patchEntity($user, $this->request->getData());
            if ($this->getUsersTable()->save($user)) {
                $this->Flash->success(__('The user has been saved.'));
                return $this->redirect(['action' => 'addUsers']);
            } else {
                $errors = array_values(array_values($user->getErrors()));
                foreach ($errors as $key => $err) {
                    foreach ($err as $key1 => $err1) {
                        $this->Flash->error($err1);
                    }
                }
            }
        }
        $roles = $this->getRolesTable()
            ->find('list', array('keyField' => 'id', 'valueField' => 'role'))
            ->toArray();
        $schoolRoles = $this->getSchoolRolesTable()->getIdNameMap();
        $learningpaths = $this->getLearningpathsTable()
            ->find('list', array('keyField' => 'id', 'valueField' => 'label'))
            ->toArray();
        $learningspeed = $this->getLearningspeedTable()
            ->find('list', array('keyField' => 'id', 'valueField' => 'label'))
            ->toArray();
        $showSchoolTable = false;
        $this->set(compact(
            'user',
            'roles',
            'schoolRoles',
            'learningpaths',
            'learningspeed',
            'showSchoolTable'
        ));
    }

    //for edit the user
    public function edit($id = null)
    {

        if ($id == null) {
            $id = $this->Authentication->getResult()->getData()['id'];
        }
        $user = $this->getUsersTable()->get($id, ['contain' => []]);
        $learningpaths = $this->getLearningpathsTable()
            ->find('list', array('keyField' => 'id', 'valueField' => 'label'))
            ->toArray();
        $learningspeed = $this->getLearningspeedTable()
            ->find('list', array('keyField' => 'id', 'valueField' => 'label'))
            ->toArray();
        $oldUser = clone $user;
        $oldUser['platform_role'] = $this->getRolesTable()->getRoleName($oldUser['role_id']);
        $oldUser['learning_path'] = $learningpaths[$user['learningpath_id']];
        $oldUser['learning_speed'] = $learningspeed[$user['learningspeed_id']];

        if ($this->request->is(['PATCH', 'POST', 'PUT'])) {
            $data = $this->request->getData();
            $userData = $this->getUsersTable()->get($id);
            $user->name = $data['name'];
            $user->email = $data['email'];
            $user->role_id = $data['role_id'];
            if (!empty($data['dob'])) {
                $user->dob = $data['dob'];
            }
            $user['learningspeed_id'] = $data['learningspeed_id'];
            $user['learningpath_id'] = $data['learningpath_id'];
            $user['learning_path'] = $learningpaths[$data['learningpath_id']];
            $user['learning_speed'] = $learningspeed[$data['learningspeed_id']];
            if (!empty($data['password'])) {
                $user->password = $data['password'];
            }
            if ($this->getUsersTable()->save($user)) {
                $user['platform_role'] = $this->getRolesTable()->getRoleName($user['role_id']);
                $map = [
                    'name' => 'name',
                    'email' => 'email',
                    'learning_speed' => 'learning speed',
                    'learning_path' => 'learning path',
                    'platform_role' => 'platform role'
                ];
                $updateMsg = $this->createUpdateMessage($oldUser, $user, $map);
                if (!empty($updateMsg)) {
                    $updateMsg = "The user's " . $updateMsg . ".";
                } else {
                    $updateMsg = "The user's info was not changed";
                }
                $this->Flash->success(__($updateMsg));
            } else {
                $this->Flash->error(__('The user could not be saved. Please, try again.'));
            }
            Cache::clear();
            return $this->redirect('/admin/users/edit/' . $user['id']);
        }
        // hide user password
        $user->password = '';

        // create template variables
        $condition = array();
        if (isset($_GET['q']) && $_GET['q'] != null) {
            $condition['Schools.name LIKE'] = '%' . $_GET['q'] . '%';
        }
        $this->paginate = [
            'conditions' => $condition
        ];
        $schools = $this->getSchoolsTable()->find()->toArray();
        $enlistedSchools = $this->getSchoolUsersTable()
            ->find()
            ->where(['user_id' => $id])
            ->contain(['Schools'])
            ->all()
            ->toArray();
        $roles = $this->getRolesTable()
            ->find('list', array('keyField' => 'id', 'valueField' => 'role'))
            ->toArray();
        $schoolRoles = $this->getSchoolRolesTable()->getIdNameMap();
        $schools = $this->getSchoolsTable()
            ->find('list', array('keyField' => 'id', 'valueField' => 'name'))
            ->toArray();

        $showSchoolTable = $id !== null;

        $this->set(compact(
            'user',
            'roles',
            'schoolRoles',
            'learningpaths',
            'learningspeed',
            'enlistedSchools',
            'showSchoolTable',
            'schools'
        ));
        $this->render('add_users');
    }

    public function addSchoolUser(string $globalUserId): Response
    {
        $data = $this->request->getData();
        $user = $this->getSchoolUsersTable()->newEmptyEntity();
        $user = $this->getSchoolUsersTable()->patchEntity($user, $data);
        $school = $this->getSchoolsTable()->get($data['school_id']);
        $schoolText = '';
        if (!empty($school)) {
            $schoolText = 'the ' . $school['name'] . ' school';
        } else {
            $schoolText = 'school ' . $data['school_id'];
        }

        if ($this->getSchoolUsersTable()->save($user)) {
            $this->Flash->success(__(
                'The user has been added to ' . $schoolText . ' as a '
                    . $this->getSchoolRolesTable()->getRoleName($data['role_id'])
            ));
        } else {
            $this->Flash->error(__('The user could added to school. Please try again.'));
        }

        return $this->redirect('/admin/users/edit/' . $globalUserId);
    }

    public function editSchoolUser($globalId, $schoolUserId): Response
    {
        $data = $this->request->getData();
        $schoolUser = $this->getSchoolUsersTable()->find()
            ->where(['SchoolUsers.id' => $schoolUserId])
            ->contain(["Schools"])
            ->first();
        $oldUser = [
            'f_name' => $schoolUser['f_name'],
            'l_name' => $schoolUser['l_name'],
            'school_role' => $this->getSchoolRolesTable()->getRoleName($schoolUser['role_id'])
        ];
        $updateMsg = ''; // for Flash message
        $addComma = false; // for adding appropriate commas in Flash message

        // ensure school user if found
        if (empty($schoolUser)) {
            Log::error("editSchoolUser error: Couldn't find school user with id " . $schoolUserId);
            return $this->redirect('/admin/users/edit/' . $globalId);
        }

        // save user
        $schoolUser = $this->getSchoolUsersTable()->patchEntity($schoolUser, $data);
        $schoolUser = $this->getSchoolUsersTable()->save($schoolUser);
        if (empty($schoolUser)) {
            $this->Flash->error(__('The user could not be updated. Please try again.'));
            return $this->redirect('/admin/users/edit/' . $globalId);
        }
        $schoolUser['school_role'] = $this->getSchoolRolesTable()->getRoleName($schoolUser['role_id']);

        // create update message if something changed
        $map = [
            'f_name' => 'first name',
            'l_name' => 'last name',
            'school_role' => 'school role'
        ];
        $updateMsg = $this->createUpdateMessage($oldUser, $schoolUser, $map);

        // create message saying that nothing was
        if (empty($updateMsg)) {
            $updateMsg = "The user's " . $schoolUser->school->name . " school info was not changed.";
        } else {
            $updateMsg = "The user's " . $updateMsg . " for the " . $schoolUser->school->name . " school.";
        }

        // display success flash message
        $this->Flash->success(__($updateMsg));

        // refresh edit user page
        return $this->redirect('/admin/users/edit/' . $globalId);
    }

    protected function createUpdateMessage($old, $new, array $map): string
    {
        $msg = '';
        $addComma = false;
        $index = 0;

        foreach ($map as $key => $value) {
            if ($old[$key] != $new[$key]) {
                // add comma if needed
                if ($addComma) {
                    $msg .= ', ';
                }
                // add message for current item
                $msg .= $value . ' was updated to ' . $new[$key];
                $addComma = true;
            }
            $index++;
        }

        return $msg;
    }

    public function deleteSchoolUser(string $schoolUserId): Response
    {
        $user = $this->getSchoolUsersTable()->get($schoolUserId);

        if ($user != null) {
            if ($this->getSchoolUsersTable()->delete($user)) {
                $this->Flash->success(__('The user has been deleted from the school.'));
            } else {
                $this->Flash->error(__('The user could not be deleted from the school. Please, try again.'));
            }
        }
        return $this->redirect('/admin/users/edit/' . $user->user_id);
    }

    //for delete the user
    public function delete($id = null)
    {
        //Hard delete
        $user = $this->getUsersTable()->get($id);
        if ($this->getUsersTable()->delete($user)) {
            $this->Flash->success(__('The user has been deleted.'));
        } else {
            $this->Flash->error(__('The user could not be deleted. Please, try again.'));
        }

        //Soft Delete
//        $user = $this->getUsersTable()->get($id);
//        $user->is_delete = 1;
//        if ($this->getUsersTable()->save($user)) {
//            $this->Flash->success(__('The user has been deleted.'));
//        } else {
//            $this->Flash->error(__('The user could not be deleted. Please, try again.'));
//        }

        return $this->redirect(['action' => 'userList']);
    }

    //for change the user status
    public function status($id = null, $status = 0)
    {
        $user = $this->getUsersTable()->get($id);
        $user->is_active = $status;
        if ($this->getUsersTable()->save($user)) {
            $this->Flash->success(__('The user successfully Updated.'));
        } else {
            $this->Flash->error(__('User Updation Failure. Please, try again.'));
        }
        return $this->redirect(['action' => 'userList']);
    }

    //for dashboard function
    public function dashboard()
    {
        /* lastst 5 user */
        $users = $this->getUsersTable()->find()->where(['is_delete' => 0])->limit(5);
        $usercount = $this->getUsersTable()->find()->where(['is_delete' => 0])->count();

        /* latest 5 path */
        $paths = $this->getLearningpathsTable()->find()->limit(5);
        $pathscount = $this->getLearningpathsTable()->find()->count();

        /* latest 5 speed */
        $speeds = $this->getLearningspeedTable()->find()->limit(5);
        $speedscount = $this->getLearningspeedTable()->find()->count();

        $this->set(compact('users', 'paths', 'speeds', 'speedscount', 'pathscount', 'usercount'));
    }

    //for admin logout
    public function logout()
    {
        // return $this->redirect($this->Auth->logout());
        $this->Authentication->logout();
        return $this->redirect(['controller' => 'Users', 'action' => 'login']);
    }

    //for admin login*/
    public function login()
    {
        $this->Authorization->skipAuthorization();
        $this->viewBuilder()->setLayout('login');

        $auth = $this->Authentication->getResult();
        $user = $this->getAuthUser();

        // If user was authenticated, and is authorized, redirect them to the dashbaord
        $privilegedRoleIds = $this->getRolesTable()->getRoleIdsThatCanAccessAllPaths();
        if ($auth->isValid() && !empty($user)) {
            if (in_array($user->role_id, $privilegedRoleIds)) {
                $targetUrl = $this->Authentication->getLoginRedirect() ?? '/admin/users/dashboard';
                return $this->redirect($targetUrl);
            } else {
                $this->Flash->error(__('You must be an admin or content developer to access this resource.'));
                $this->Authentication->logout();
                return;
            }
        }

        if ($this->request->is('post') && !$auth->isValid()) {
            Log::error("login error: " . json_encode($auth->getErrors())
                . ". status: " . $auth->getStatus());
            $this->Flash->error('Invalid username or password.');
            return;
        }

        if ($this->request->is('post')) {
            if (!empty($user)) {
                $element = array();
                $element['last_logged'] = date('Y-m-d H:i:s');
                $this->updateUserData($user['id'], $element);
                return $this->redirect($this->Authentication->getLoginRedirect());
            }
            $this->Flash->error(__('Invalid username or password, try again'));
        }
    }

    public function updateUserData($userId, $data)
    {
        $id = $userId;
        $user = $this->getUsersTable()->get($id, ['contain' => []]);
        $userData = $this->getUsersTable()->get($id);
        foreach ($data as $key => $value) {
            if (isset($value) && $value != '') {
                $user->$key = $value;
            }
        }
        if ($this->getUsersTable()->save($user)) {
            return $user;
        } else {
            return false;
        }
    }

    //for builk user action

    public function forgotPassword($email = null)
    {
        $getMailData = $this->Mail->createMailTemplate('forget_password', 'dts.sushobhan@dreamztech.com');
        $getMailData['param']['email'] = 'dts.sushobhan@dreamztech.com';
        $this->sendMail($getMailData, $getMailData['template'], $getMailData['layout']);
    }

    //general function for update the user. param id,name,dob(YYYY-mm-dd),learningspeed_id,learningpath_id,

    public function bulkAction()
    {
        $data = $_POST;


        $action = $_POST['action'];
        $response = array();
        if ($action == 'deleteuser') {
            $ids = $_POST['ids'];
            $resuser = array();
            foreach ($ids as $id) {
                $user = $this->getUsersTable()->get($id);
                if ($this->getUsersTable()->delete($user)) {
                    $resuser[] = array('id' => $id, 'status' => 'Deleted');
                } else {
                     $resuser[] = array('id' => $id, 'status' => 'Not Deleted');
                }
            }
            $response = array('status' => 'success', 'data' => $resuser);
            echo json_encode($response);
        }

        if ($action == 'resetPassword') {
            $ids = $_POST['ids'];
            $resuser = array();
            foreach ($ids as $id) {
                $user = $this->getUsersTable()->get($id);
                $user->password = $data['password'];
                if ($this->getUsersTable()->save($user)) {
                    $resuser[] = array('id' => $id, 'status' => 'password Reset Successfully.');
                } else {
                    $resuser[] = array('id' => $id, 'status' => 'password Updation failed.');
                }
            }
            $response = array('status' => 'success', 'data' => $resuser);
            echo json_encode($response);
        }
        die;
    }
}
