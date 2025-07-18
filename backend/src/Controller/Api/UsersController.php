<?php

namespace App\Controller\Api;

use App\Controller\Api\Login\AppleLoginService;
use App\Controller\Api\Login\CleverLoginService;
use App\Exceptions\RequiredFieldException;
use App\Controller\Api\Login\FacebookLoginService;
use App\Controller\Api\Login\GoogleLoginService;
use App\Controller\Api\Login\InvalidLoginService;
use App\Controller\Api\Login\SiteLoginService;
use App\Lib\CleverLibrary;
use App\Lib\HttpStatusCode;
use App\Lib\UtilLibrary;
use Cake\Core\Configure;
use Cake\Http\Exception\UnauthorizedException;
use Cake\Utility\Security;
use Firebase\JWT\JWT;
use GuzzleHttp\Exception\GuzzleException;
use Authentication\Authenticator\ResultInterface;
use Cake\I18n\FrozenTime;
use Cake\Log\Log;
use Cake\Database\Query;
use App\Lib\RegionPolicy;
use Exception;

define("ONE_YEAR_SEC", 31536000);

class UsersController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('Mail');
        $this->loadComponent('FilesCommon');
        $this->Authentication->allowUnauthenticated([
            'token', 'signup', 'login', 'forgotPassword', 'resetPasswordToken',
            'checkEmail', 'captchaResponse', 'resetPassword',
            'contactUs', 'signInWithClever', 'teacherChangePassword', 'getRegionPolicy',
        ]);
    }

    // Api function for all the users and get user by id.
    public function getUser(string $id = null)
    {
        if (empty($id)) {
            $this->sendApiData(false, "No id passed.", [], HttpStatusCode::BAD_REQUEST);
        }

        $this->validateUserIsInteractingWithTheirOwnData($id);

        $user = $this->getUserById($id);
        if (empty($user)) {
            $this->sendApiData(
                false,
                'No user found with ID of ' . $id . '.',
                [],
                HttpStatusCode::BAD_REQUEST
            );
            return;
        }
        if ($user[0]['learningpath_id'] != '') {
            $user[0]['badge'] = $this->getBadgeByUser($id);
        } else {
            $user[0]['badge'] = [];
        }

        $this->sendApiData(true, 'Got user ' . $id . ' successfully.', $user);
    }

    /**
     * Returns array with:
     *  - first object contains
     *      - id, name, userimages
     *      - usersetting (FullProfileImageUrl, profile_desc)
     *      - badge (socialpoint, firebadges, levelbadge (image))
     *
     */
    public function getPublicUser(string $id = null)
    {
        if (empty($id)) {
            $this->sendApiData(false, "No id passed.", [], HttpStatusCode::BAD_REQUEST);
        }

        // Convert id to int
        $id = intval($id);

        // Get user with public data
        $users = $this->getUsersTable()
            ->find('all')
            ->contain(['Usersetting', 'Userimages'])
            ->select([
                'Users.id',
                'Users.dob',
                'Users.learningpath_id',
                'Users.name',
                'Usersetting.profile_picture',
                'Usersetting.profile_desc'
            ])
            ->where(['Users.id' => $id])
            ->toArray();

        // Validate user exists
        if (empty($users || empty($users[0]))) {
            $this->sendApiData(false, 'No user found with ID of ' . $id . '.', [], HttpStatusCode::BAD_REQUEST);
            return;
        }

        $userDob = ($users[0]['dob'])->toDateString();

        if ($users[0]['approximate_age'] < RegionPolicy::selfConsentMinAge()) {
            $this->sendApiData(false, 'User not publicly accessible due to age.', [], HttpStatusCode::FORBIDDEN);
            return;
        }

        // Remove dob from user data. We just needed it to check age.
        unset($users[0]['dob']);

        // Get user's badge info
        if ($users[0]['learningpath_id'] != '') {
            $users[0]['badge'] = $this->getBadgeByUser($id);
        } else {
            $users[0]['badge'] = [];
        }

        $this->sendApiData(true, 'Got public user successfully.', [ 'user' => $users[0] ]);
    }

    // Api function for update the user by id Method POST.
    public function updateUser()
    {
        if (!$this->request->is('post')) {
            $msg = 'Profile update faliure. Please try again later.';
            $this->sendApiData(false, $msg, [], HttpStatusCode::BAD_REQUEST);
            return;
        }

        $data = $this->request->getData();

        try {
            $this->validateRequest($data, ['id']);
            $this->validateUserIsInteractingWithTheirOwnData($data['id']);

            $id = $data['id'];

            $result = $this->updateUserData($id, $data);
            if (!empty($result)) {
                $msg = 'Profile successfully updated.';
                $status = true;
                $users = $this->getUserById($id);
                $data = $users;
                $code = HttpStatusCode::OK;
            } else {
                $msg = 'Profile update faliure.';
                $status = false;
                $data = array();
                $code = HttpStatusCode::INTERNAL_SERVER_ERROR;
            }
        } catch (Exception $e) {
            $msg = $e->getMessage();
            $status = false;
            $data = [];
            $code = HttpStatusCode::BAD_REQUEST;
        }

        $this->sendApiData($status, $msg, $data, $code);
    }

    // Api function for update the user by id Method POST.
    public function updateUserSetting()
    {
        if (!$this->request->is('post')) {
            $msg = 'Profile update faliure. Please try again later.';
            $this->sendApiData(false, $msg, [], HttpStatusCode::BAD_REQUEST);
            return;
        }

        $data = $this->request->getData();

        // Lint profile description
        if (!empty($data['profile_desc'])) {
            if ($this->getBannedWordsTable()->presentInText($data['profile_desc'])) {
                $this->sendApiData(false, "Profile cannot contain inappropriate language", array());
                return;
            }
        }

        $this->validateRequest($data, ['id']);
        $this->validateUserIsInteractingWithTheirOwnData($data['id']);

        $id = $data['id'];
        $result = $this->updateuserSettings($id, $data);
        if ($result) {
            $msg = 'Profile successfully updated.';
            $status = true;
            $users = $this->getUserById($id);
            $data = $users;
            $code = HttpStatusCode::OK;
        } else {
            $msg = 'Profile update faliure.';
            $status = false;
            $data = array();
            $code = HttpStatusCode::INTERNAL_SERVER_ERROR;
        }

        $this->sendApiData($status, $msg, $data, $code);
    }

    public function updateuserSettings($userId, $data)
    {
        $id = $userId;
        $Usersetting = $this->getUserSettingsTable()
            ->find('all', ['contain' => []])
            ->where(['Usersettings.user_id' => $userId])
            ->toArray();
        if ($Usersetting) {
            $userData = $this->getUserSettingsTable()->get($Usersetting[0]['id']);
            $data['user_id'] = $Usersetting[0]['user_id'];
            $data['id'] = $Usersetting[0]['id'];
        } else {
            $user = $this->getUserSettingsTable()->newEmptyEntity();
            $data['user_id'] = $data['id'];
            unset($data['id']);
            $userData = $this->getUserSettingsTable()->patchEntity($user, $data);
        }
        if (isset($data['profile_picture'])) {
            if (isset($data["is_app"]) && $data["is_app"] == 1) {
                $temp = base64_decode($data['profile_picture']);
                $tempfile = "temp.jpg";
                $file["name"] = $file["tmp_name"] = $tempfile;
                $setNewFileName = $this->randomString();
                $ext = substr(strtolower(strrchr($tempfile, '.')), 1);
                file_put_contents(WWW_ROOT . 'img/ProfileImage/' . $setNewFileName . '.' . $ext, $temp);
                $uploadResult = @$this->FilesCommon->reSizeImage(
                    'image',
                    200,
                    200,
                    $setNewFileName,
                    $ext,
                    'img/ProfileImage/'
                );

                if ($uploadResult['awsupload']) {
                    $data['aws_profile_link'] = $uploadResult['awsupload']['result']['ObjectURL'];
                }
                $data['profile_picture'] = $setNewFileName . '.' . $ext;
            } elseif (!empty($data['profile_picture']->getClientFilename())) {
                if ($data['profile_picture']->getError() !== UPLOAD_ERR_OK) {
                    return false;
                }
                $param = array();
                $uploadResult = $this->FilesCommon->uploadFile($data['profile_picture'], $param, 'PROFILEIMAGE');
                $data['profile_picture'] = $uploadResult['filename'];
                if ($uploadResult['awsupload']) {
                    $data['aws_profile_link'] = $uploadResult['awsupload']['result']['ObjectURL'];
                }
            }
        }

        foreach ($data as $key => $value) {
            if (isset($value) && $value != '') {
                $userData->$key = $value;
            }
        }

        if ($this->getUserSettingsTable()->save($userData)) {
            return $userData;
        } else {
            return false;
        }
    }

    // Setup captcha response

    private function randomString()
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $randstring = '';
        for ($i = 0; $i < 15; $i++) {
            $randstring .= $characters[rand(0, 35)];
        }
        return $randstring . time();
    }

    /**
     * Signup a new user.
     *
     * @return void
     */
    public function signup(): void
    {
        $authUser = $this->getAuthUser();
        $loginService = new SiteLoginService($authUser, $this->getRequest());
        $response = $loginService->signup();
        $this->sendApiData($response->getStatus(), $response->getMessage(), $response->getResult());
    }

    private function runningOnLocalhost(): bool
    {
        return str_contains(Configure::read('FROENTEND_LINK'), 'localhost');
    }

    public function captchaResponse()
    {
        if ($this->request->is('post')) {
            $captchaSecret = $this->runningOnLocalhost()
                ? '6LfJRxwgAAAAALqirrfdsgTreZ42XHsPfzEXl1pn'
                : '6LfZ48wfAAAAAEGu6mNdh32DVS_5eCxoH5dz72QR';
            $data = $this->request->getData();
            $headers = array();
            $headers[] = "Content-Type: application/x-www-form-urlencoded";

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, "response=" . $data['token'] . "&secret=" . $captchaSecret);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $result = curl_exec($ch);
            if (curl_errno($ch)) {
                echo 'captchaResponse Curl Error:' . curl_error($ch);
            }
            curl_close($ch);

            $this->sendApiData(true, 'Captcha Response', json_decode($result));
        }
    }

    // Api function for forgotPassword. param : Email.

    /**
     * Api function for login.
     * If user already signed up with a social login, they should still
     * be able to sign up with email and password, adding password,
     * and then log in with either method.
     * If user already signed up with email and password, they should still
     * be able to sign in with social login, just adding social login info
     * to their user account.
     * @throws RequiredFieldException
     */
    public function login()
    {
        if ($this->getRequest()->is('post')) {
            $this->validateRequest($this->request->getData(), [
                'type'
            ]);

            $payload = $this->request->getData();

            switch ($payload['type'] ?? "") {
                case "site":
                    $this->validateRequest($payload, [
                        'type', 'email', 'password',
                    ]);
                    // Only check auth for site login, since social logins don't
                    // include password in the request data, so will always fail.
                    $auth = $this->Authentication->getResult();
                    if (!$auth->isValid()) {
                        $this->sendApiData(
                            false,
                            $this->convertAuthStatusToMsg($auth->getStatus(), $payload),
                            [],
                            HttpStatusCode::BAD_REQUEST
                        );
                        return;
                    }
                    $user = $this->getAuthUser();
                    $loginService = new SiteLoginService($user, $this->getRequest());
                    break;
                case "apple":
                    $loginService = new AppleLoginService($payload);
                    break;
                case "fb":
                    $loginService = new FacebookLoginService($payload);
                    break;
                case "google":
                    $loginService = new GoogleLoginService($payload);
                    break;
                case "clever":
                    $loginService = new CleverLoginService($payload);
                    break;
                default:
                    $loginService = new InvalidLoginService($payload);
            }

            $response = $loginService->login();
            $this->sendApiData(
                $response->getStatus(),
                $response->getMessage(),
                $response->getResult(),
                $response->getStatusCode()
            );
        } else {
            Log::error($this->request->getMethod() . ' method not allowed for login');
            // TODO: Make middleware for !post request blocking
            $this->sendApiData(
                false,
                $this->request->getMethod() . ' not allowed.',
                [],
                HttpStatusCode::METHOD_NOT_ALLOWED
            );
        }
    }

    private function convertAuthStatusToMsg($status, $payload): string
    {
        switch ($status) {
            case ResultInterface::SUCCESS:
                return "Login successful";
            case ResultInterface::FAILURE_IDENTITY_NOT_FOUND:
                return "We weren't able to log you in. No account with the email "
                    . $payload['email']
                    . " was found. Please try a different email, or sign up.";
            case ResultInterface::FAILURE_CREDENTIALS_INVALID:
                $numUsersFound = $this->getUsersTable()->find()->where(['email' => $payload['email']])->count();
                if ($numUsersFound > 0) {
                    return "Hmm, that's not the correct password. Please try again.";
                } else {
                    return "We weren't able to log you in. No account with the email "
                    . $payload['email']
                    . " was found. Please try a different email, or sign up.";
                }
            case ResultInterface::FAILURE_CREDENTIALS_MISSING:
                if (empty($payload['email'])) {
                    return "We weren't able to log you in, due to a missing email";
                } elseif (empty($payload['password'])) {
                    return "We weren't able to log you in, due to a missing password.";
                } else {
                    return "We weren't able to log you in, for an unknown reason. "
                        . "Use the Feedback tab to ask tech support.";
                }
            case ResultInterface::FAILURE_OTHER:
            default:
                return "Hmm, we weren't able to log you in. Use the Feedback tab to ask tech support.";
        }
    }


  // Api function for forgotPassword. param : Email.
    public function forgotPassword()
    {
        if ($this->request->is('post')) {
            $email = $this->request->getData('email');
            if (isset($email) && $email != '') {
                $count = $this->getUsersTable()->find()->where(['email' => $email])->count();
                if ($count == 0) {
                    $this->sendApiData(false, 'This Email is not registered.', array());
                } else {
                    $user = $this->getUsersTable()->find()->where(['email' => $email])->toArray();
                    $id = $user[0]['id'];
                    $toemail = $user[0]['email'];
                    $name = $user[0]['name'];
                    $token = base64_encode($this->generatePasswordToken($id));

                    $tokenrow = $this->getPasswordresetTable()->find()->where(['Passwordreset.email' => $toemail])->first();
                    if (empty($tokenrow)) {
                        $pr = $this->getPasswordresetTable()->newEmptyEntity();
                        $pr->email = $toemail;
                        $pr->token = $token;
                        $pr->created_at = date('Y-m-d H:i:s');
                        $pr->updated_at = date('Y-m-d H:i:s');
                        $this->getPasswordresetTable()->save($pr);
                        //$param = array('email' => $toemail, 'name' => $name, 'password' => $password);
                        $param = array(
                            'email' => $toemail,
                            'name' => $name,
                            'link' => Configure::read('FROENTEND_LINK') . 'change-password/' . urlencode($token));
                        $getMailData = $this->Mail->createMailTemplate('forget_password', $param);
                        $getMailData['param']['email'] = $toemail;
                        $this->sendMail($getMailData, $getMailData['template'], $getMailData['layout']);
                        $this->sendApiData(
                            true,
                            'Password reset instructions have been sent to your '
                            . 'email address. You have 24 hours to complete the '
                            . 'request.If you do not receive it in your email '
                            . 'inbox in the next 5 minutes, please check your '
                            . 'SPAM folder.',
                            array()
                        );
                    } else {
                        $tokenrow->email = $toemail;
                        $tokenrow->token = $token;
                        $tokenrow->created_at = date('Y-m-d H:i:s');
                        $tokenrow->updated_at = date('Y-m-d H:i:s');
                        $this->getPasswordresetTable()->save($tokenrow);
                        //$param = array('email' => $toemail, 'name' => $name, 'password' => $password);
                        $param = array(
                            'email' => $toemail,
                            'name' => $name,
                            'link' => Configure::read('FROENTEND_LINK') . 'change-password/' . urlencode($token));
                        $getMailData = $this->Mail->createMailTemplate('forget_password', $param);
                        $getMailData['param']['email'] = $toemail;
                        $this->sendMail($getMailData, $getMailData['template'], $getMailData['layout']);
                        $this->sendApiData(
                            true,
                            'Password reset instructions have been sent to your '
                            . 'email address. You have 24 hours to complete the '
                            . 'request. If you do not receive it in your email '
                            . 'inbox in the next 5 minutes, please check your SPAM folder.',
                            array()
                        );
                    }
                }
            } else {
                $this->sendApiData(false, 'Please enter valid email', array());
            }
        }
    }

    /**
     * Generate a unique hash / token.
     * @param Object User
     * @return Object User
     */
    public function generatePasswordToken($user)
    {
        if (empty($user)) {
            return null;
        }
        $token = md5(uniqid($user, true));

        return $token;
    }

    /**
     * Resets a users password, given their current and new password.
     * If the request contains 'email' and 'password', the Auth component
     * will be used to authenticate the user.
     * Otherwise and direct compare with happen between the password the
     * entered into the form as their current password and their hashed password
     * in the database.
     */
    public function resetPassword()
    {
        if (!$this->request->is('post')) {
            $msg = 'Failed to reset password. Try again later.';
            $this->sendApiData(false, $msg, HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
        // make sure request has required fields
        $this->validateRequest($this->request->getData(), [
            'id',
            'current_password',
            'new_password',
        ]);

        // extract POST data
        $data = $this->request->getData();
        $newPassword = $data['new_password'];
        $user = [];

        // create response messages
        $badCurrPassMsg = 'Please enter your current password correctly.';
        $badUserIdMsg = "Server error. Couldn't find user with ID of " . $data['id'];
        $badSaveMsg = "Server error. Failed to save new password";
        $successMsg = "Password updated successfully";

        // validate user email and password
        if (!empty($data['email']) && !empty($data['password'])) {
            // authenticate user if email and password provided in POST data
            $user = $this->getAuthUser();
            if (empty($user) || empty($user['id'])) {
                $this->sendApiData(false, $badCurrPassMsg, HttpStatusCode::BAD_REQUEST);
                return;
            }
        } else {
            // get user from table so we have their email
            $user = $this->getUsersTable()->get($data['id']);
            if (empty($user) || empty($user['id'])) {
                Log::error($badUserIdMsg);
                $this->sendApiData(false, $badUserIdMsg, HttpStatusCode::BAD_REQUEST);
                return;
            }
            // authenticate user by comparing passwords
            if (!password_verify($data['current_password'], $user['password'])) {
                $this->sendApiData(false, $badCurrPassMsg, HttpStatusCode::BAD_REQUEST);
                return;
            }
        }
        // update user's password
        $newData = ['password' => $newPassword];
        $result = $this->updateUserData($user['id'], $newData);

        if (empty($result)) {
            Log::error($badSaveMsg);
            $this->sendApiData(false, $badSaveMsg, HttpStatusCode::INTERNAL_SERVER_ERROR);
            return;
        }

        // get the updated user object
        $userData = $this->getUserById($user['id']);
        if (empty($userData)) {
            Log::error($badUserIdMsg);
            $this->sendApiData(false, $badUserIdMsg, HttpStatusCode::BAD_REQUEST);
            return;
        }

        // respond with success and updated user object
        $this->sendApiData(true, $successMsg, $userData);
    }

    /**
     * Allow user to reset password if $token is valid.
     * @return
     */
    public function resetPasswordToken()
    {
        $token = $this->request->getData('token');
        $newPassword = $this->request->getData('new_password');
        $row = $this->getPasswordresetTable()->find()->where(['token' => $token])->first();
        //die('sss');
        if (!empty($row)) {
            if ($this->validToken($row->created_at->timestamp)) {
                $email = $row->email;
                $user = $this->getUsersTable()->find()->where(['email' => $email])->first();
                //print_r($user);
                $is_active = $user->is_active;
                if ($is_active == 1) {
                    $user->password = $newPassword;
                    $this->getUsersTable()->save($user);
                    $result = $this->getPasswordresetTable()->delete($row);
                    $this->sendApiData(true, 'Your password has been successfully reset.', array());
                } else {
                    $this->sendApiData(false, 'The profile is deactiveted. please contact administrator.', array());
                }
            } else {
                $this->sendApiData(false, 'Your token has expired.', array());
            }
        } else {
            $this->sendApiData(false, 'Your token is not valid.', array());
        }
    }

    // Api function for checkmail. param : Email.

    /**
     * Validate token created at time.
     * @param String $token_created_at
     * @return bool
     */
    public function validToken($token_created_at)
    {
        $expired = $token_created_at + 86400;
        $time = strtotime("now");
        if ($time < $expired) {
            return true;
        }
        return false;
    }

    //Api function for token. param : Email,password,type,social_id

    public function teacherChangePassword()
    {
        $teacher_id = $this->request->getData('teacher_id');
        $student_user_id = $this->request->getData('student_user_id');
        $new_password = $this->request->getData('new_password');
        $teacher_school_ids = $this->getSchoolUsersTable()
            ->find()
            ->select(['school_id'])
            ->where(['role_id' => $this->getSchoolRolesTable()->getRoleId(UtilLibrary::SCHOOL_ROLE_TEACHER_STR)]);
        $student_school_ids = $this->getSchoolUsersTable()
            ->find()
            ->select(['school_id'])
            ->where([
                'role_id' => $this->getSchoolRolesTable()->getRoleId(UtilLibrary::SCHOOL_ROLE_STUDENT_STR),
                'school_id IN' => $teacher_school_ids
            ]);
        //die('sss');
        if (!empty($student_school_ids)) {
            $user = $this->getUsersTable()->find()->where(['id' => $student_user_id])->first();
            //print_r($user);
            $is_active = $user->is_active;
            if ($is_active == 1) {
                $user->password = $new_password;
                $this->getUsersTable()->save($user);
                $this->sendApiData(true, 'Your password has been successfully reset.', array());
            } else {
                $this->sendApiData(false, 'The profile is deactiveted. please contact administrator.', array());
            }
        } else {
            $this->sendApiData(false, 'You do not have permissions to edit this password.', array());
        }
    }

    // check friend and status

    public function checkEmail()
    {
        $email = $this->request->getData('email');
        if (isset($email) && $email != '') {
            $count = $this->getUsersTable()->find()->where(['email' => $email])->count();
            if ($count == 0) {
                $this->sendApiData(false, 'This email is not registered.', array());
            } else {
                $this->sendApiData(true, 'This email is already associated with another account.', array());
            }
        } else {
            $this->sendApiData(false, 'Please enter a valid email.', array());
        }
    }

    /**
     * @throws RequiredFieldException
     */
    public function token()
    {
        $user = $this->getAuthUser();

        $data = $this->getRequest()->getData();
        $this->validateRequest($data, [
            'type'
        ]);

        if (isset($data['id'])) {
            $count = $this->getUsersTable()
                ->find('all', ['contain' => ['Usersetting', 'Userimages']])
                ->where(['Users.id =' => $data['id']])
                ->where(['Users.is_active' => 1])
                ->count();
            if ($count == 0) {
                $success = false;
                $message = 'Your account deactivated succesfully.';
                $this->set(compact('success', 'message'));
                $this->viewBuilder()->setOption('serialize', ['success', 'message']);
                return;
            }
        }
        if ($data['type'] !== "site") {
            $this->validateRequest($data, [
                'social_id'
            ]);
        }
        switch ($data['type']) {
            case "site":
                if (!$user) {
                    throw new UnauthorizedException('Invalid username or password');
                }
                break;
            case "fb":
                $user = $this->getUsersTable()
                    ->find()
                    ->where(['fb_id' => $data['social_id'], 'is_active' => 1, 'is_delete' => 0])
                    ->first();
                break;
            case "apple":
                $user = $this->getUsersTable()
                    ->find()
                    ->where(['apple_id' => $data['social_id'], 'is_active' => 1, 'is_delete' => 0])
                    ->first();
                break;
            case "google":
                $user = $this->getUsersTable()
                    ->find()
                    ->where(['google_id' => $data['social_id'], 'is_active' => 1, 'is_delete' => 0])
                    ->first();
                break;
            case "clever":
                $user = $this->getUsersTable()
                    ->find()
                    ->where(['clever_id' => $data['social_id'], 'is_active' => 1, 'is_delete' => 0])
                    ->first();
                break;
            default:
                throw new UnauthorizedException('Invalid login type ' . $data['type']);
        }
        if (isset($user['id'])) {
            $this->set([
                'success' => true,
                'data' => [
                    'token' => JWT::encode(
                        [
                            'sub' => $user['id'],
                            'exp' => time() + ONE_YEAR_SEC
                        ],
                        Security::getSalt(),
                        'HS256'
                    )
                ]
            ]);
            $this->viewBuilder()->setOption('serialize', ['success', 'data']);
        } else {
            throw new UnauthorizedException('Invalid username or password');
        }
    }

    // get all users for find friends

    public function checkFriend()
    {
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $userId = $data['id'];
            $fbStatus = (isset($data['fb_status'])
                && $data['fb_status'] != '') ? $data['fb_status'] : null;
            $googleStatus = (isset($data['google_status'])
                && $data['google_status'] != '') ? $data['google_status'] : null;
            $fbData = (isset($data['fb_data'])
                && $data['fb_data'] != '') ? $data['fb_data'] : '';
            $googleData = (isset($data['google_data'])
                && $data['google_data'] != '') ? $data['google_data'] : '';

            /* get FB friends and data */
            if ($fbStatus) {
                $fbDataToArray = json_decode($fbData, true);
                $fbFriendsFlag = $fbDataToArray['friends']['data'];
                $fbId = $fbDataToArray['id'];
                $fbFriends = array();
                if (!empty($fbFriendsFlag)) {
                    foreach ($fbFriendsFlag as $fbfriendsElement) {
                        $element = array(
                            'id' => $fbfriendsElement['id'],
                            'imageData' => $fbfriendsElement['picture']['data'],
                            'name' => $fbfriendsElement['name']
                        );
                        array_push($fbFriends, $element);
                    }
                }
            }


            if ($googleStatus) {
                /* get google friends and data */
                $googleDataToArray = json_decode($googleData, true);
                $googleFriendsFlag = $googleDataToArray['entry'];
                $user = $this->getUserById($userId);
                $googleId = $user[0]['google_id'];
                $googleFriends = array();
                foreach ($googleFriendsFlag as $googlefriendsElement) {
                    if (
                        isset($googlefriendsElement['gd$email'][0]['address'])
                        && $googlefriendsElement['gd$email'][0]['address'] != ''
                        && filter_var($googlefriendsElement['gd$email'][0]['address'], FILTER_VALIDATE_EMAIL)
                    ) {
                        $element = array(
                            'id' => $googlefriendsElement['gd$etag'],
                            'imageData' => $googlefriendsElement['gd$etag'],
                            'name' => (
                                isset($googlefriendsElement['title']['$t'])
                                    && $googlefriendsElement['title']['$t'] != '')
                                ? $googlefriendsElement['title']['$t']
                                : $googlefriendsElement['gd$email'][0]['address'],
                            'email' => $googlefriendsElement['gd$email'][0]['address']
                        );
                        array_push($googleFriends, $element);
                    }
                }
            }


            /* get friends list */
            $totalFriends = array();
            $friends = $this->getFriends($userId);
            if ($fbStatus == 1) {
                $data = array('fb_status' => 1);
                $result = $this->updateUserData($userId, $data);
                $siteFbFriends = array_filter($friends, array($this, 'haveFbId'));
                foreach ($fbFriends as $fUser) {
                    $fbId = $fUser['id'];
                    $sitefbfriend = array_filter($siteFbFriends, function ($elem) use ($fbId) {
                        if ($elem['fb_id'] == $fbId) {
                            return true;
                        }
                    });
                    if (empty($sitefbfriend)) {
                        $element = array(
                            'name' => $fUser['name'],
                            'image' => $fUser['imageData'],
                            'fbId' => $fUser['id'],
                            'friendstatus' => false,
                        );
                    } else {
                        $element = array(
                            'name' => $fUser['name'],
                            'image' => $fUser['imageData'],
                            'fbId' => $fUser['id'],
                            'friendstatus' => true,
                        );
                    }
                    array_push($totalFriends, $element);
                }
            }
            if ($googleStatus == 1) {
                $data = array('google_status' => 1);
                $result = $this->updateUserData($userId, $data);
                $siteGoogleFriends = array_filter($friends, array($this, 'haveGoogleId'));
                foreach ($googleFriends as $gUser) {
                    $googleId = $gUser['email'];
                    $sitegooglefriend = array_filter($siteGoogleFriends, function ($elem) use ($googleId) {
                        if ($elem['email'] == $googleId) {
                            return true;
                        }
                    });
                    if (empty($sitegooglefriend)) {
                        $element = array(
                            'name' => $gUser['name'],
                            'email' => $gUser['email'],
                            'image' => $gUser['imageData'],
                            'googleId' => $gUser['id'],
                            'friendstatus' => false,
                        );
                    } else {
                        $element = array(
                            'name' => $gUser['name'],
                            'email' => $gUser['email'],
                            'image' => $gUser['imageData'],
                            'googleId' => $gUser['id'],
                            'friendstatus' => true,
                        );
                    }
                    array_push($totalFriends, $element);
                }
            }
            $totalFriends = array_filter($totalFriends, array($this, 'isSiteSocialUser'));

            //print_r($totalFriends);
            $totalFriends = array_values($totalFriends);
            //die;
            $this->sendApiData(true, 'Friends Result return succesfully.', $totalFriends);
        }
    }

    // get friends for find friends

    public function getFriends($userId)
    {
        $id = $userId;
        $users = $this->getFriendsTable()->find('all', ['contain' => ['User', 'Friend']])
            ->where(['OR' => [
                ['friend_id =' => $userId], ['user_id' => $userId]]])
            ->toArray();
        $friends = array();
        foreach ($users as $u) {
            if ($u->user->id != $userId) {
                array_push($friends, $u->user);
            }
            if ($u->friend->id != $userId) {
                array_push($friends, $u->friend);
            }
        }
        return $friends;
    }

    // inviteFriend and status

    public function getLeaderBoardData()
    {
        $data = $this->request->getData();
        $response = [];
        $userId = $data['user_id'] ?? null;

        if (!$userId) {
            $this->sendApiData(false, 'User id is required.', []);
            return;
        }

        $userData = $this->getUserById($userId);
        if (empty($userData)) {
            $this->sendApiData(false, 'User not found.', []);
            return;
        }

        // Get settings
        $settings = $this->getSitesettingsTable()->getPrefixedKeys("setting_");

        $friends = $this->getFriends($userId);
        $friendsIds = array_map(function($friend) {
            return $friend['id'];
        }, $friends);

        // Add self to friends
        $friendsIds[] = $userId;
        $userData = $userData[0];

        // Retrieve users with public leaderboard enabled or current user
        $conditions = [
            'OR' => [
                ['Usersetting.public_leaderboard' => 1],
                ['UserPoints.user_id' => $userId]
            ],
            'Users.name IS NOT' => 'trial',
        ];

        if ($settings['setting_minors_can_access_leaderboard'] === '1') {
            $conditions['Users.approximate_age >='] = RegionPolicy::selfConsentMinAge();
        } else {
            $conditions['Users.approximate_age >='] = RegionPolicy::adultMinAge();
        }

        // Get top 10 users by total points
        $queryParams = [
            'group' => ['UserPoints.user_id'],
            'contain' => ['Users' => [ 'Usersetting' ]],
            'order' => ['UserPoints.total_score' => 'DESC'],
            'limit' => 10,
            'conditions' => $conditions
        ];
        $query = $this->getUserPointsTable()->find('all', $queryParams);

        // Aggrate total points for each user
        // profile_picture and aws_profile_link are used to get virtual field FullProfileImageUrl
        $response['top_users'] = $query
            ->select([
                'totalpoint' => $query->func()->max('UserPoints.total_score'),
                'user_id',
                'Users.name',
                'Usersetting.profile_picture',
                'Usersetting.aws_profile_link',
            ])
            ->order(['totalpoint' => 'DESC'])
            ->toArray();

        // Get friends leaderboard
        $queryParams['conditions']['UserPoints.user_id IN'] = $friendsIds;
        $query = $this->getUserPointsTable()->find('all', $queryParams);

        // profile_picture and aws_profile_link are used to get virtual field FullProfileImageUrl
        $response['friends'] = $query
            ->select([
                'totalpoint' => $query->func()->max('UserPoints.total_score'),
                'user_id',
                'Users.name',
                'Usersetting.profile_picture',
                'Usersetting.aws_profile_link',
            ])
            ->order(['totalpoint' => 'DESC'])
            ->toArray();

        $response['leaderboard_flag'] = $userData['usersetting']['public_leaderboard'];

        $this->sendApiData(true, 'Leaderboard returned succesfully.', $response);
    }

    // inviteFriend and status

    public function getUsersForFriends()
    {
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $userId = $data['user_id'];
            $friendQuery = $data['query'] ?? "";


            // $limit = isset($data['limit']) ? $data['limit'] : 20;
            // $page = isset($data['page']) ? $data['page'] : 1;
            $friendsIDList = [];
            $allUsers = [];

            $users = $this->findFriends($userId, $friendQuery);
            $friends = $this->getFriends($userId);
            $friendsIDList = array_map(fn ($friend) => $friend['id'], $friends);

            foreach ($users as $key => $user) {
                $age = $user['approximate_age'] ?? 0;
                if ($age >= RegionPolicy::adultMinAge()) {
                    $element = [
                        'id' => $user['id'],
                        'name' => $user['name'],
                        'email' => $user['email'],
                        'image' => $user['usersetting']['FullProfileImageUrl'],
                        'profile_desc' => $user['usersetting']['profile_desc'],
                        'googleId' => $user['google_id'] ?? $user['email'],
                        'fbId' => $user['fb_id'] ??  null,
                        'friendstatus' => in_array($user['id'], $friendsIDList),
                        'userType' => isset($user['google_id']) ? 'google' : (isset($user['fb_id']) ? 'fb' : 'site'),
                    ];
                    $allUsers[] = $element;
                }
            }

            $responseArray = array(
                'resultSet' => $allUsers,
                'totalCount' => $users->count()
            );

            $this->sendApiData(true, 'Users list returned successfully.', $responseArray);
        }
    }

    public function findFriends($userId, $friendQuery): Query
    {
        $settings = $this->getSitesettingsTable()->getPrefixedKeys("setting_");

        $conditions = [
            'Users.is_active' => 1,
            'Users.id !=' => $userId,
            'Usersetting.public_profile' => 1,
        ];

        if ($settings['setting_minors_can_access_village'] === '1') {
            $conditions['Users.approximate_age >='] = RegionPolicy::selfConsentMinAge();
        } else {
            $conditions['Users.approximate_age >='] = RegionPolicy::adultMinAge();
        }

        if ($friendQuery != "") {
            $conditions['OR'] = [
                "Users.name LIKE" => "%" . $friendQuery . "%",
                "Users.email LIKE" => "%" . $friendQuery . "%"
            ];
        }

        $options = array(
            'limit' => 25,
            'contain' => array('Usersetting'),
            'order' => ['Users.last_logged' => 'DESC'],
            'conditions' => $conditions
        );

        return $this->getUsersTable()->find('all', $options);
    }

    // api for upload gallery images based on userid

    public function getUsersFriends()
    {
        $data = $this->request->getData();
        $userId = $data['user_id'];
        $friends = $this->getFriends($userId);
        $friendsIDList = array();
        foreach ($friends as $friend) {
            array_push($friendsIDList, $friend['id']);
        }

        $ForumIdAccess = $this->getForumIdAccessByUserId($userId);

        if (!empty($friendsIDList)) {
            $allUsers = array();
            $options = array(
                'contain' => array('Usersetting', 'PostReply' => function ($q) use ($ForumIdAccess) {
                    if (!empty($ForumIdAccess)) {
                        return $q
                            ->where(['PostReply.forum_id IN' => $ForumIdAccess])
                            ->where(['PostReply.flag_id IS' => null]);
                    } else {
                        return $q->where(['PostReply.flag_id IS' => null]);
                    }
                }, 'Posts' => function ($q) use ($ForumIdAccess) {
                    if (!empty($ForumIdAccess)) {
                        return $q
                            ->where(['Posts.forum_id IN' => $ForumIdAccess])
                            ->where(['Posts.flag_id IS' => null]);
                    } else {
                        return $q->where(['Posts.flag_id IS' => null]);
                    }
                }),
                'conditions' => array(
                    'Users.is_active' => 1,
                    'Users.id !=' => $userId,
                    'Usersetting.public_profile' => 1,
                    'Users.id IN' => $friendsIDList
                )
            );
            $users = $this->getUsersTable()->find('all', $options);
            foreach ($users as $key => $user) {
                $element = array(
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'image' => $user['usersetting']['FullProfileImageUrl'],
                    'profile_desc' => $user['usersetting']['profile_desc'],
                    'googleId' => $user['google_id'] ?? $user['email'],
                    'fbId' => $user['fb_id'] ?? null,
                    'friendstatus' => in_array($user['id'], $friendsIDList),
                    'userType' => isset($user['google_id']) ? 'google' : (isset($user['fb_id']) ? 'fb' : 'site'),
                    'postCounter' => count($user['posts']),
                    'replyCounter' => $user['post_reply'],
                );
                array_push($allUsers, $element);
            }
            $responseArray = array(
                'resultSet' => $allUsers,
                'totalCount' => $users->count()
            );
        } else {
            $responseArray = array(
                'resultSet' => array(),
                'totalCount' => 0
            );
        }
        $this->sendApiData(true, 'Users list returned successfully.', $responseArray);
    }

    // api for upload gallery images based on userid

    public function inviteFriend()
    {
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $userId = $data['id'];
            $googleData = $data['google_data'];

            /* get google friends and data */
            $googleDataToArray = json_decode($googleData, true);
            $googleFriendsFlag = $googleDataToArray['entry'];
            $user = $this->getUserById($userId);
            $googleId = $user[0]['google_id'];
            $googleFriends = array();
            foreach ($googleFriendsFlag as $googlefriendsElement) {
                if (
                    isset($googlefriendsElement['gd$email'][0]['address'])
                    && $googlefriendsElement['gd$email'][0]['address'] != ''
                    && filter_var($googlefriendsElement['gd$email'][0]['address'], FILTER_VALIDATE_EMAIL)
                ) {
                    $element = array(
                        'id' => $googlefriendsElement['gd$etag'],
                        'imageData' => $googlefriendsElement['gd$etag'],
                        'name' => (
                            isset($googlefriendsElement['title']['$t'])
                                && $googlefriendsElement['title']['$t'] != '')
                            ? $googlefriendsElement['title']['$t']
                            : $googlefriendsElement['gd$email'][0]['address'],
                        'email' => $googlefriendsElement['gd$email'][0]['address']
                    );
                    array_push($googleFriends, $element);
                }
            }

            /* get friends list */
            $totalFriends = array();
            $friends = $this->getFriends($userId);
            $siteGoogleFriends = array_filter($friends, array($this, 'haveGoogleId'));
            foreach ($googleFriends as $gUser) {
                $googleId = $gUser['email'];
                $sitegooglefriend = array_filter($siteGoogleFriends, function ($elem) use ($googleId) {
                    return $elem['email'] == $googleId;
                });
                if (empty($sitegooglefriend)) {
                    $element = array(
                        'name' => $gUser['name'],
                        'email' => $gUser['email'],
                        'image' => $gUser['imageData'],
                        'googleId' => $gUser['id'],
                        'friendstatus' => false,
                    );
                } else {
                    $element = array(
                        'name' => $gUser['name'],
                        'email' => $gUser['email'],
                        'image' => $gUser['imageData'],
                        'googleId' => $gUser['id'],
                        'friendstatus' => true,
                    );
                }
                array_push($totalFriends, $element);
            }
            $totalFriends = array_filter($totalFriends, array($this, 'isSiteNonGoogleUser'));
            $totalFriends = array_values($totalFriends);
            $this->sendApiData(true, 'Friends Result return succesfully.', $totalFriends);
        }
    }

    // Api function for addOrRemoveFriend . param : status,userId,friendsFbId,friendsgoogleId

    public function emailInviteFriend()
    {
        $data = $this->request->getData();
        $userid = $data['user_id'];
        $emails = $data['email'];
        $textmessage = $data['message'];
        $emailArr = explode(",", $emails);
        $users = $this->get('Users');
        $user = $users->get($userid);
        foreach ($emailArr as $email) {
            if (isset($email) && $email != '') {
                $param = array('email' => $email, 'name' => $user['name'], 'message' => $textmessage);
                $getMailData = $this->Mail->createMailTemplate('invite_mail', $param);
                $getMailData['param']['email'] = $email;
                $this->sendMail($getMailData, $getMailData['template'], $getMailData['layout']);
            }
        }
        $this->sendApiData(true, 'Invite Friends Successfully.', array());
    }

    // Api function for Delete an account buy id

    public function uploadGalleryImage()
    {
        $data = $this->request->getData();
        ///print_r($data);die;
        $userid = $data['id'];
        if (isset($data["is_app"]) && $data["is_app"] == 1) {
            $temp = base64_decode($data['image']);
            $tempfile = "temp.jpg";
            $file["name"] = $file["tmp_name"] = $tempfile;
            $setNewFileName = $this->randomString();
            $ext = substr(strtolower(strrchr($tempfile, '.')), 1);
            //place image
            file_put_contents(WWW_ROOT . 'img/GalleryImage/' . $setNewFileName . '.' . $ext, $temp);
            $filename = $setNewFileName . '.' . $ext;
            //saveimage
            $data = array('user_id' => $userid, 'image' => $filename);
            if (Configure::read('AWSUPLOAD')) {
                $aws = $this->FilesCommon->uploadFileToAws(
                    WWW_ROOT . 'img/GalleryImage/' . $setNewFileName . '.'
                    . $ext,
                    $setNewFileName . '.' . $ext,
                    'GALLERYIMAGE'
                );
                $data['aws_link'] = $aws['result']['ObjectURL'];
            } else {
                $data['aws_link'] = null;
            }

            $user = $this->getUserimagesTable()->newEmptyEntity();
            $user = $this->getUserimagesTable()->patchEntity($user, $data);
            $this->getUserimagesTable()->save($user);
            $user = $this->getUserById($userid);
            $this->sendApiData(true, 'Images are successfully uploaded', $user);
        } else {
            //check validation
            $validData = true;
            $typeFormat = explode("/", $data['image']->getClientMediaType());
            $type = $typeFormat[0];
            $format = $typeFormat[1];
            if ($type != 'image') {
                $validData = false;
            }
            if ($validData) {
                //upload image
                $param = array();
                $uploadResult = $this->FilesCommon->uploadFile($data['image'], $param, 'GALLERYIMAGE');
                //saveimage
                $user = $this->getUserimagesTable()->newEmptyEntity();
                $data = array(
                    'user_id' => $userid,
                    'image' => $uploadResult['filename'],
                    'aws_link' => $uploadResult['awsupload']['result']['ObjectURL']);
                $user = $this->getUserimagesTable()->patchEntity($user, $data);
                $this->getUserimagesTable()->save($user);
                $user = $this->getUserById($userid);
                $this->sendApiData(true, 'Images are successfully uploaded', $user);
            } else {
                $this->sendApiData(false, 'please upload valid image.', array());
            }
        }
    }

    public function deleteGalleryImage()
    {
        $data = $this->request->getData();
        $imageId = $data['imageid'];
        $image = $this->getUserimagesTable()->get($imageId);
        if ($this->getUserimagesTable()->delete($image)) {
            $this->sendApiData(true, 'Image is successfully deleted', array());
        } else {
            $this->sendApiData(false, 'Image Deletion failure.Please try again.', array());
        }
    }

    //general function for update the user setting.

    public function addOrRemoveFriend()
    {
        $data = $this->request->getData();
        $status = $data['status'];
        $userID = $data['userId'];
        $friendId = $data['friendId'];

        if (isset($friendId) && $friendId != '') {
            $friendsId = $friendId;
        } else {
            if (isset($data['friendsFbId']) && $data['friendsFbId'] != '') {
                $friendsFbId = $data['friendsFbId'];
                $fUser = $this->getUsersTable()->find()->where(['fb_id' => $friendsFbId])->toArray();
                $friendsId = $fUser[0]['id'];
            } elseif (isset($data['friendsgoogleId']) && $data['friendsgoogleId'] != '') {
                $friendsGoogleId = $data['friendsgoogleId'];
                $fUser = $this->getUsersTable()->find()->where(['email' => $friendsGoogleId])->toArray();
                $friendsId = $fUser[0]['id'];
            }
        }

        /* get friends user Id by fb */

        if ($status == 1) {
            $FriendsRow = $this->getFriendsTable()->find()
                ->where(['OR' => [
                    ['AND' => [['friend_id' => $data['userId']], ['user_id' => $friendsId]]],
                    ['AND' => [['friend_id' => $friendsId], ['user_id' => $data['userId']]]]
                ]])->toArray();
            if (empty($FriendsRow)) {
                $user = $this->getFriendsTable()->newEmptyEntity();
                $Frienddata = array('user_id' => $userID, 'friend_id' => $friendsId);
                $user = $this->getFriendsTable()->patchEntity($user, $Frienddata);
                if ($this->getFriendsTable()->save($user)) {
                    $this->sendApiData(true, 'Friends added succesfully.', array());
                } else {
                    $this->sendApiData(false, 'Friends added failure.', array());
                }
            } else {
                $this->sendApiData(false, 'Friends already addded.', array());
            }
        } elseif ($status == 0) {
            $FriendsRow = $this->getFriendsTable()->find()
                ->where(['OR' => [
                    ['AND' => [['friend_id' => $data['userId']], ['user_id' => $friendsId]]],
                    ['AND' => [['friend_id' => $friendsId], ['user_id' => $data['userId']]]]
                ]])->toArray();
            if (!empty($FriendsRow)) {
                $getRow = $this->getFriendsTable()->get($FriendsRow[0]['id']);
                if ($this->getFriendsTable()->delete($getRow)) {
                    $this->sendApiData(true, 'Friends remove successfully.', array());
                } else {
                    $this->sendApiData(false, 'Friends remove failure.', array());
                }
            } else {
                $this->sendApiData(false, 'No Friend record Found.', array());
            }
        }
    }

    public function deactivateAccount()
    {
        $data = $this->request->getData();
        $id = $data['id'];
        $user = $this->getUsersTable()->get($id);
        $user->is_active = 0;
        if ($this->getUsersTable()->save($user)) {
            $this->sendApiData(true, 'Your account deactivated succesfully.', array());
        } else {
            $this->sendApiData(false, 'Your account deactivation failure', array());
        }
    }

    public function deleteAccount()
    {
        // Validate DELETE request data
        $data = $this->request->getData();
        $this->validateRequest($data, ['userId']);

        // Validate user is logged in
        $authUser = $this->getAuthUser();
        if (empty($authUser)) {
            $this->sendApiData(false, 'Please log in first.', [], HttpStatusCode::UNAUTHORIZED);
            return;
        }

        // Validate user deleting is same as user to delete
        if ($authUser->id !== $data['userId']) {
            $this->sendApiData(false, 'You can only delete your own account.', [], HttpStatusCode::UNAUTHORIZED);
            return;
        }

        // Get user from database so we can delete it
        $user = $this->getUsersTable()->get($data['userId']);
        if (empty($user)) {
            $this->sendApiData(false, 'Failed to find user to delete.', [], HttpStatusCode::BAD_REQUEST);
            return;
        }

        // Delete the user
        if (!$this->getUsersTable()->delete($user)) {
            $this->sendApiData(false, 'Failed to delete the user', [], HttpStatusCode::INTERNAL_SERVER_ERROR);
            return;
        }

        Log::notice("Deleting user with id " . $user->id);
        $this->sendApiData(true, 'Deleted user ('
            . 'id: ' . $user->id
            . ', name: ' . $user->name
            . ', email: ' . $user->email
            . ')'
        );
    }

    //general function for get user friend by id. param id,

    public function contactUs()
    {
        $data = $this->request->getData();
        $appName = Configure::read('App.name') ?? 'eLearning';

        $param = array(
            'name' => ucfirst($data['name']),
            'email' => strtolower($data['email']),
            'message' => $data['problemdetails'],
            'issue' => $data['issue'],
            'app_name' => $appName,
        );

        $getMailData = $this->Mail->createMailTemplate('contact_mail', $param);

        $siteSettings = $this->getSitesettingsTable()
            ->find('list', [ 'keyField' => 'key', 'valueField' => 'value' ])
            ->toArray();

        if (empty($siteSettings['site_email'])) {
            $this->sendApiData(false, 'Site email not configured.', [], HttpStatusCode::INTERNAL_SERVER_ERROR);
            return;
        }

        $getMailData['param']['email'] = $siteSettings['site_email'];
        $this->sendMail($getMailData, $getMailData['template'], $getMailData['layout']);
        $this->sendApiData(true, 'Thanks for contacting us. The '
            . Configure::read('App.name') . ' Team will contact you soon.', array());
    }

    //general function for check user have fb id or not,

    public function haveFbId($friend)
    {
        if (isset($friend['fb_id']) && $friend['fb_id'] != '') {
            return true;
        } else {
            return false;
        }
    }

    //general function for check site user or not,
    public function isSiteSocialUser($friend)
    {
        if (isset($friend['fbId']) && $friend['fbId'] != '') {
            $count = $this->getUsersTable()->find()->where(['fb_id' => $friend['fbId']])->count();
            if ($count == 0) {
                return false;
            } else {
                return true;
            }
        } elseif (isset($friend['email']) && $friend['email'] != '') {
            $count = $this->getUsersTable()->find()->where(['email' => $friend['email']])->count();
            if ($count == 0) {
                return false;
            } else {
                return true;
            }
        }
    }

    //general function for check site user or not,
    public function isSiteNonGoogleUser($friend)
    {
        if (isset($friend['googleId']) && $friend['googleId'] != '') {
            $count = $this->getUsersTable()->find()->where(['google_id' => $friend['googleId']])->count();
            if ($count == 0) {
                return true;
            } else {
                return false;
            }
        }
    }

    //general function for check user have google id or not,
    public function haveGoogleId($friend)
    {
        if (isset($friend['google_id']) && $friend['google_id'] != '') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Save the user's acceptance of the agreements.
     * Ensures the user is interacting with their own data.
     */
    public function saveAgreementsAcceptance()
    {
        $data = $this->request->getData();
        $this->validateRequest($data, ['user_id']);

        $this->validateUserIsInteractingWithTheirOwnData($data['user_id']);

        $userId = $data['user_id'];

        $user = $this->getUsersTable()->get($userId);
        if (empty($user)) {
            $this->sendApiData(false, 'User not found.', []);
            return;
        }

        $user->agreements_accepted = 1;
        if ($this->getUsersTable()->save($user)) {
            $usersExtended = $this->getUserById($userId);
            if (empty($usersExtended)) {
                $this->sendApiData(false, 'Agreements accepted successfully, but error in retrieving user.', [], HttpStatusCode::INTERNAL_SERVER_ERROR);
                return;
            }
            $this->sendApiData(true, 'Agreements accepted successfully', [ 'user' => $usersExtended[0] ]);
        } else {
            $this->sendApiData(false, 'Agreements acceptance failure.', [], HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Notifies a parent via email of their child's account creation.
     *
     * @api {POST} /api/users/notifyParent Notify Parent
     * @apiDescription Notify a parent via email of their child's account creation.
     *
     * @apiHeader {String} Content-Type application/json
     * @apiHeader {String} Accept application/json
     * @apiHeader {String} Authorization Bearer {token}
     *
     * @apiParam {String} user_id The user's id
     * @apiParam {String} parents_email The parent's email address
     *
     * @apiSuccess {Boolean} success True if the email was sent successfully
     * @apiSuccess {String} message The message indicating the email was sent successfully
     * @apiSuccess {Object[]} data An empty array
     *
     * @apiSuccessExample {json} Success Response:
     * {
     *    "status": true,
     *    "message": "Email sent successfully.",
     *    "data": []
     * }
     *
     * @apiError {Boolean} success False if the email was not sent successfully
     * @apiError {String} message The error message
     * @apiError {Object[]} data An empty array
     *
     * @apiErrorExample {json} Error Response:
     * {
     *    "status": false,
     *    "message": "User not found.",
     *    "data": []
     * }
     */
    public function notifyParent()
    {
        $data = $this->request->getData();
        $this->validateRequest($data, ['user_id', 'parents_email']);

        $this->validateUserIsInteractingWithTheirOwnData($data['user_id']);

        $userId = $data['user_id'];

        // Get user Entity so we can get the user's name and email
        $user = $this->getUsersTable()->get($userId);
        if (empty($user)) {
            $this->sendApiData(false, 'User not found.', []);
            return;
        }

        $siteSettings = $this->getSitesettingsTable()
            ->find('list', [ 'keyField' => 'key', 'valueField' => 'value' ])
            ->toArray();

        if (empty($siteSettings['site_email'])) {
            $this->sendApiData(
                false, 'Site email not configured.', [], HttpStatusCode::INTERNAL_SERVER_ERROR
            );
            return;
        }

        // Replacement parameters for the email template
        $param = [
            'username' => $user->name,
            'childs_email' => strtolower($user->email),
            'app_name' => Configure::read('App.name'),
            'support_email' => $siteSettings['site_email'],
        ];

        $mailData = $this->Mail->createMailTemplate('parent_notification', $param);

        // Set sendTo email address
        $mailData['param']['email'] = strtolower($data['parents_email']);

        $this->sendMail($mailData, $mailData['template'], $mailData['layout']);

        $this->sendApiData(true, 'Email sent successfully.');
    }

    public function getRegionPolicy()
    {
        $this->sendApiData(true, 'Region policy returned successfully.', [
            'selfConsentMinAge' => RegionPolicy::selfConsentMinAge(),
            'adultMinAge' => RegionPolicy::adultMinAge(),
        ]);
    }
}
