<?php

namespace User\Controller\Component;
use Cake\Auth\Storage;

use Cake\Controller\Component;
use Cake\Event\Event;
use Cake\Core\Configure;
use Cake\Network\Http\Client;
use Cake\ORM\TableRegistry;
use Cake\I18n\Time;
use AppServiceUtil\Utility\AppServiceUtil;
use Cake\Network\Email\Email;
use Cake\Auth\DefaultPasswordHasher;
use Cake\View\View;

/**
 * User common Component used to perform user related activities
 */
class UserCommonComponent extends Component {

    public $UserTblObj = NULL;
    public $controller = '';
    public $components = ['AppServiceUtil.UtilCommon'];
    public $globalAdminRoleId = 1;
    public $MUserAuditsTblObj = NULL;
    public $MUserLoginHistory = NULL;

    /*
     * Initialize function to setup some initial valrs and call method to do intial function
     * @access public
     * @params $config array to populate initialize time config vars to user component
     */

    public function initialize(array $config) {
        parent::initialize($config);
        $this->session = $this->request->session();
        $this->UserTblObj = TableRegistry::get('User.User');
        $this->MUserAuditsTblObj = TableRegistry::get('User.MUserAudits');
        $this->MUserLoginHistory = TableRegistry::get('User.MUserLoginHistory');
        $this->UserWorkspace = TableRegistry::get('WorkSpace.UserWorkspace');

        $this->controller = $this->_registry->getController();

        // Do few auth setting before to login in system			
        $this->controller->Auth->config('authenticate', [
            'Form' => [
                'userModel' => 'User.User',
                'fields' => ['username' => 'username', 'password' => 'password'],
                'scope' => ['User.is_active' => 1, 'User.is_deleted' => 0]
            ]
        ]);
    }

    public function beforeFilter(Event $event) {
        if (Configure::read('AUTHENTICATION.IS_EXTERNAL_AUTHENTICATION')) {
            if (!isset($this->http) || empty($this->http))
                $this->http = new Client();
            $this->requestSSO();
        }
    }

    /*
     * Function to check if someone is logged into the system or not
     * @access public
     * @params None checks logged in user by Auth Session
     * @return true/false 
     */

    public function checkUserLoggedIn() {
        $user = $this->controller->Auth->User();
        if ($user) {
            $user_id = $this->controller->Auth->User('id');
            return $user_id;
        } else {
            return FALSE;
        }
    }

    /*
     * Function to logout current user and return true/false
     */

    public function logoutUser() {
        if (Configure::read('AUTHENTICATION.IS_EXTERNAL_AUTHENTICATION')) { // process external logout
            if (!isset($this->http) || empty($this->http))
                $this->http = new Client();
            $this->maintainUserLoginHistory(false);
            $this->doRemoteLogout();
        } else {
            if ($this->controller->Auth->logout()) {
                $this->maintainUserLoginHistory(false);
                $this->manageUserActivity('', '', true); //update user activity 
                session_unset();
                return TRUE;
            }
        }
        return FALSE;
    }

    /*
     * Function to process form based login
     * @Params : Login details array (username, password)
     */

    public function processFormLogin() {

        $username = (isset($this->request->data['username'])) ? $this->request->data['username'] : '';
        $password = (isset($this->request->data['password'])) ? AppServiceUtil::decryptData($this->request->data['password']) : '';
        $this->request->data['password'] = $password;
        if (!empty($username) && !empty($password)) {

            // Login into system		
            $user = $this->controller->Auth->identify();
            // If selected user found
            if ($user) {
                $this->manageUserActivity($user, SUCCESS);
                            //   pr($user);die;

                //$userId = $this->controller->Auth->user('id'); 
               
                $workspaceDt = $this->setUserExtraDetails($user['id']);//set user extra details in session
                $user['Workspace']=$workspaceDt['prepareWkSpace'];
                $user['ISGLOBALADMIN']=$workspaceDt['ISGLOBALADMIN'];
                $user['WorkspaceIds']=$workspaceDt['WorkspaceIds'];
                $this->controller->Auth->setUser($user);
                $userDt = $this->controller->Auth->user();
                $this->setUserExtraDetails($userDt['id']); //set user extra details in session

                $this->maintainUserLoginHistory(true);
                //$returnData['user'] = $this->controller->Auth->user();
                //$returnData['user']['id'] = session_id();
                // Convert fields name
                /* $returnData['user'] = AppServiceUtil::backendFrontendFieldsMap('Users', $userDt, true, array('workspace_id',
                  'created_user_id','modified_user_id','un_agency','is_deleted','is_active',
                  'last_login_ip','last_login_date','contact_number','created','modified','id','address' ));
                  //pr($returnData);die;
                 */
                $returnData['user'] = ['firstName' => $userDt['first_name'],
                    'lastName' => $userDt['first_name'], 'firstName' => $userDt['last_name'],
                    'userName' => $userDt['username'], 'organization' => $userDt['organization'], 'email' => $userDt['email'],
                    'permissions' => []
                ];


                return $returnData;
            } else {

                $this->manageUserActivity(['username' => $_POST['username']], FAILED);

                return AppServiceUtil::errResponse('INVALID_LOGIN_CRED');
            }
        } else {
            return AppServiceUtil::errResponse('MISSING_PARAMETERS');
        }
    }

    /**
     * get user basic details
     * @param int $user_id to have selected user id
     * @return array $userBasicDetails contain user basic details
     * @access public
     */
    public function getUserBasicDetails($user_id = null) {
        $returnData = [];

        // If user id found
        if (!empty($user_id)) {

            // Mkae query to get details
            $userBasicDetails = $this->UserTblObj->getFirst(['id', 'email', 'username', 'first_name', 'last_name'], ['id' => $user_id]);

            // If selected details found
            if (!empty($userBasicDetails)) {
                $returnData['user'] = $userBasicDetails;
            }
            // If selected details not found
            else {
                $returnData = ['hasError' => 1, 'err' => ['errCode' => 'NO_DATA_FOUND']];
            }
        }
        // If user id not found
        else {
            $returnData = ['hasError' => 1, 'err' => ['errCode' => 'NO_DATA_FOUND']];
        }
        return $returnData;
    }

    /**
     * getRecords method for Users
     * @param array $conditions Conditions on which to search. {DEFAULT : empty}
     * @param array $fields Fields to fetch. {DEFAULT : empty}
     * @return array users list
     */
    public function getUsers() {
        $fields = $conditions = $extra = [];
        $type = 'all';
        $postData = $this->request->data;
        // Get all roles for current user
        $workspaces = $this->getRoleLoggedUser();
        //If logged in user is global admin them show him/her list of country admins
        if (in_array(GLOBAL_ADMIN_WORKSPACE_ID, $workspaces)) {
            return $this->UserTblObj->getCountryAdmins();
        } else { // Country specific list
            echo 'special case';
            die;
            // If fields are specified
            if (!empty($postData['fields'])) {
                $fields = $postData['fields'];
            }
            // Debugging request
            if (!empty($postData['extra'])) {
                $extra = $postData['extra'];
            }
            if (!empty($roleData['workspace_id'])) {
                $conditions = ['workspace_id' => $roleData['workspace_id']];
            }
            // Get list of country specific users
            return $this->UserTblObj->getRecords($fields, $conditions, $type, $extra);
        }
        return [];
    }

    /**
     * Method to add new user in appication
     * @param None     
     * @return user id if successfull or error array if false
     */
    public function addUser() {
        $postedUserData = $this->request->data;

        // Convert fields name
        $postedUserData = AppServiceUtil::backendFrontendFieldsMap('Users', $postedUserData, false, array());
        // New user created by loggedIn user
        $userId = $this->checkUserLoggedIn();

        //System releted info 
        $postedUserData['last_login_date'] = '2016-04-13 11:21:03';
        $postedUserData['last_login_ip'] = '';
        $postedUserData['created_user_id'] = $userId;
        if (empty($postedUserData['username']) && (!empty($postedUserData['email']))) {
            $postedUserData['username'] = $postedUserData['email'];
        }
        // Save user info [Auto validation In model]
        $result = $newUserId = $this->UserTblObj->insertUser($postedUserData);
        if (empty($newUserId['hasError'])) {
            //$result=$this->sendActivationLink($newUserId, $postedUserData['email'], $postedUserData['first_name'],__('Activation link'));
        }
        return $result;
    }

    /**
     * update User method for Users
     * @param None
     * @return true if successfull or error array if false
     */
    public function updateUser() {
        $postedData = $this->request->data;
        $postedData = AppServiceUtil::backendFrontendFieldsMap('Users', $postedData, false, array());
        $result = $this->UserTblObj->updateUser($postedData);
        return $result;
    }

    public function getFirst($fields = [], $conditions = []) {
        return $this->UserTblObj->getFirst($fields, $conditions);
    }

    /*
     * 
     * method to update password on forgot password  activation link
     * @data posted info 
     */

    public function accountActivation() {
        $data = $this->request->data();
        /* $data['key']='YWJjZCMjIyMjLTItYWJjZCMjIyo5OSo=';
          $data['password']='Admin@1234';
          $data['confirmpassword']='Admin@1234'; */

        $validate = $this->validateLink($data); // validate posted data 

        if (isset($validate['error'])) {
            return AppServiceUtil::errResponse($validate['error']);
        }
        $actkey = $data['key'];
        //$actkey ='YWJjZCMjIyMjLTIzMC1hYmNkIyMjKjk5Kg==';
        $requestdata = array();
        $encodedstring = trim($actkey);
        $decodedstring = base64_decode($encodedstring);
        $explodestring = explode('-', $decodedstring);

        $requestdata['modified_user_id'] = $requestdata['id'] = $userId = $explodestring[1];
        $password = $requestdata['password'] = trim($data['password']);
        $requestdata['is_active'] = 1; // Activate user 

        $errorCode = $this->checkPasswordStrength($password); // check pwd strength 
        if ($errorCode === true) {
            $retValue = $this->UserTblObj->updateUser($requestdata, false);
            if ($retValue > 0) {
                // success 
                return $retValue;
            } else {
                return AppServiceUtil::errResponse('SERVER_ERROR');     // password not updated due to server error   
            }
        } else {
            return AppServiceUtil::errResponse($errorCode); //password strength 
        }
    }

    /*
     * forgotPassword method sends password reset link on email 
     * @params username
     * 
     */

    public function forgotPassword() {

        $username = (isset($this->request->data['userName'])) ? $this->request->data['userName'] : 'moz';

        if (!empty($username)) {
            //get user details using username 
            $userData = $this->UserTblObj->getFirst(['id', 'first_name', 'email', 'last_name'], ['username' => $username]);
            if (isset($userData['id']) && $userData['id'] > 0) {

                $userData['is_active'] = '0';
                $name = $userData['first_name'] . DELEM3 . $userData['last_name'];
                $userId = $userData['id'];
                $this->UserTblObj->updateUser($userData, true); //update status for activation link	
                $status = $this->sendActivationLink($userId, $userData['email'], $name, FORGOTPASSWORD_SUBJECT);
            } else {
                return AppServiceUtil::errResponse('INVALID_USERNAME');
            }
        } else {
            return AppServiceUtil::errResponse('MISSING_PARAMETERS');
        }
    }

    /*
      sending activation link
      @params $userId is user id , $email recievers email $name recievers name
      @params $subject is for subject of email
     */

    public function sendActivationLink($userId, $email, $name, $subject) {
        $appName = APP_NAME;

        $encodedstring = base64_encode(SALT_PREFIX1 . '-' . $userId . '-' . SALT_PREFIX2);
        $website_base_url = _WEBSITE_URL . "#/UserActivation/$encodedstring";
        $configData = ['name' => $name, 'url' => $website_base_url, 'appName' => $appName];

        $view = new View($this->request, $this->response, null);
        $view->set('configData', $configData);
        $view->viewPath = 'Email'; // Directory inside view directory to search for .ctp files
        $view->layout = false; //$view->layout='ajax'; // layout to use or false to disable
        $message = $view->render('User.user_email');


        $fromEmail = ADMIN_EMAIL;
        //$email ='rkapoor@avaloninfosys.com';
        //$this->UtilCommon->sendEmail($email, $fromEmail, $subject, $message, 'smtp');
        AppServiceUtil::sendEmail($email, $fromEmail, $subject, $message, 'smtp');
    }

    /**
     * method to validate activation details 
     * @param type $data
     * @return type
     */
    public function validateLink($data) {

        $actkey = (isset($data['key'])) ? $data['key'] : '';
        if (empty($actkey)) {
            return ['error' => 'KEY_BLANK']; //checks key is empty or not
        }

        if (!isset($data['password']) || empty($data['password'])) {
            return ['error' => 'MISSING_PARAMETERS']; //checks Empty password    
        }

        $encodedstring = trim($actkey);
        $decodedstring = base64_decode($encodedstring);
        $explodestring = explode(DELEM3, $decodedstring);
        if (isset($explodestring) && count($explodestring) != 3) {
            return ['error' => 'INVALID_ACTIVATION_KEY'];            //  invalid key 
        }

        if ($explodestring[0] != SALT_PREFIX1 || $explodestring[2] != SALT_PREFIX2) {
            return ['error' => 'INVALID_ACTIVATION_KEY'];            //  invalid key    
        }

        $userId = $explodestring[1];

        $activationStatus = $this->UserTblObj->getCount(['is_active' => '0', 'id' => $userId]);
        if ($activationStatus == 0)
            return ['error' => 'ACTIVATION_LNK_USED'];       //  Activation link already used
    }

    /**
     *  method to check paswword strength 
     * @param type $pwd
     */
    public function checkPasswordStrength($pwd = '') {

        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[&@#$])[A-Za-z\d$@$!%*?&]{8,}/', $pwd)) {
            return 'PWD_STRENGTH';
        }

        return true;
    }

    /**
     * method to get logged in user details 
     */
    public function getLoggedInDetails() {
        $authUserId = $this->controller->Auth->user('id');
        if ($authUserId) {
            $userDt = $this->controller->Auth->user();
            /*
              $returnData['user'] = AppServiceUtil::backendFrontendFieldsMap('Users', $userDt, true,
              array('workspace_id','created_user_id','modified_user_id','un_agency','is_deleted','is_active',
              'last_login_ip','last_login_date','contact_number','created','modified','id','address','organization', ));
             */
            $returnData['user'] = ['firstName' => $userDt['first_name'],
                'lastName' => $userDt['first_name'], 'firstName' => $userDt['last_name'],
                'userName' => $userDt['username'], 'organization' => $userDt['organization'], 'email' => $userDt['email'],
            ];
            return $returnData;
        } else {
            return AppServiceUtil::errResponse('');
        }
    }

    /**
     * manage user login activity 
     * @param type $user
     * @param type $status
     * @param $logout is default false if true update logout time 
     */
    public function manageUserActivity($user = [], $status = SUCCESS, $logout = false) {

        $ipaddress = AppServiceUtil::getIpAddress(); // get client ip address 

        if ($logout == true) {
            $auditId = $this->session->read('auditId');
            if (!empty($auditId)) {
                $auditFields = ['id' => $auditId, 'logout_date' => date('Y-m-d H:i:s')];
                $auditId = $this->MUserAuditsTblObj->updateUserActivity($auditFields);
            }
        } else {
            $auditFields = [ 'user_id' => $user['username'], 'ip_address' => $ipaddress, 'login_status' => $status, 'login_date' => date('Y-m-d H:i:s')];
            $auditId = $this->MUserAuditsTblObj->insertUserActivity($auditFields);
            $this->session->write('auditId', $auditId);
        }
    }

    /*
     * Function to get logged in user role 
     * @access public
     * @params None 
     * @return role id 
     */

    public function getRoleLoggedUser() {
        $workspaces = [];
        if (!empty($_SESSION['UserAllWorkspaceIds'])) {
            $workspaces = $_SESSION['UserAllWorkspaceIds'];
        }
        return $workspaces;
    }

    /*
     * Function to get get User Info
     * @access array
     * @params None 
     * @return user info
     */

    public function getUserInfo($params = []) {
        $response = [];
        if (!empty($params['username']) && !empty($params['password'])) {
            $verifyUserPassword = false;
            $userInfo = $this->UserTblObj->getUserDetails([
                'conditions' => [
                    'username' => $params['username'],
                ],
                'fields' => []
            ]);
            if (!empty($userInfo))
                $verifyUserPassword = (new \Cake\Auth\DefaultPasswordHasher)
                        ->check($params['password'], $userInfo['password']);

            if (!empty($userInfo) && $verifyUserPassword === true) {
                $response['success'] = true;
                $response['data'] = $userInfo;
            } else {
                $response = AppServiceUtil::errResponse('INVALID_LOGIN_CRED');
            }
        } else {
            $response = AppServiceUtil::errResponse('MISSING_PARAMETERS');
        }
        return $response;
    }

    /*
     * Function to request remote based login
     * @Params : none
     */

    public function requestSSO() {
        $currentAuth = $this->controller->Auth;
        $DFAMON_SSN_ID = isset($_COOKIE["DFAMON_SSN_ID"]) ? $_COOKIE["DFAMON_SSN_ID"] : "";
        $ssoAccessAPIURL = Configure::read('AUTHENTICATION.SSO_URL');
        $ssoLogoutURL = Configure::read('AUTHENTICATION.SSO_LOGOUT_URL');
        $isNewUser = false;
        if ($currentAuth->user() && (isset($DFAMON_SSN_ID) && !empty($DFAMON_SSN_ID))
        ) {
            //exit ('already logged in');
        } else if (!$currentAuth->user() && (isset($DFAMON_SSN_ID) && !empty($DFAMON_SSN_ID))
        ) {
            //exit ('not logged in, DFAMON_SSN_ID exists then try to login');
            $ssoResponse = $this->http->post(
                    $ssoAccessAPIURL, ['TOKENIDENTIFIER' => $DFAMON_SSN_ID], []);
            if ($ssoResponse && $ssoResponse->code == 200) {
                $returnData = json_decode($ssoResponse->body, true);
                if ($returnData && $returnData['success'] == true && !empty($returnData['tokenKey'])) {
                    // Step1 - Get token information
                    $loginUrl = $returnData['loginUrl'];
                    $tokenKey = $returnData['tokenKey'];

                    // Step2 - Decrypt token using the decrypt key
                    $tokenData = $this->decryptToken($tokenKey);

                    // Step3 - Get the user details form token
                    $userInfo = $this->extractTokenInfo($tokenData);

                    // Step4 - Check if user is in database
                    $userStatus = $this->UserTblObj->createUserIfNotExists($userInfo);

                    //Step5 - Add this user in database if not exists
                    if (isset($userStatus["status"]) && isset($userStatus["isNew"]) && $userStatus["status"] == true && $userStatus["isNew"] == true
                    ) { // User created
                        $isNewUser = true;
                    } else { // Error occured while adding user
                        $response["success"] = false;
                    }

                    // Step6 - do login
                    if (!empty($userInfo)) {
                        $this->processRemoteLogin(["username" => $userInfo["username"]], $loginUrl);
                    }
                } else {
                    // error from remote server, redirect to remote server
                    $this->doRemoteRedirect($ssoLogoutURL);
                    //$this->redirect($returnData['loginUrl']);
                }
            } else {
                // error from remote server, redirect to remote server
                $this->doRemoteRedirect($ssoLogoutURL);
            }
        } else {
            //exit('logout from product, redirect to the portal login page');
            $this->doRemoteLogout();
        }
        return;
    }

    /*
     * Function : To process remote based login
     * @Params : Login details array (username, password)
     * Remark : Because we rely on portal authentication,
     * we have username (extracted from token) then we got user details on behalf of username
     * then user's details set into auth
     */

    public function processRemoteLogin($params = [], $loginUrl = "") {
        //Configure::write('debug', 0);
        if (!empty($params) && $loginUrl != "") {
            if (isset($params["username"])) {
                $userInfo = $this->UserTblObj->getUserDetails([

                                'conditions' => ['username' => $params['username']],
                                'fields' => []
                            ]);        

                if (!empty($userInfo)) {
                     
              
                   
                    $workspaceDt = $this->setUserExtraDetails($userInfo['id']);//set user extra details in session
                    $userInfo['Workspace']=$workspaceDt['prepareWkSpace'];
                    $userInfo['ISGLOBALADMIN']=$workspaceDt['ISGLOBALADMIN'];
                    $userInfo['WorkspaceIds']=$workspaceDt['WorkspaceIds'];
                    

                    @$this->controller->Auth->setUser($userInfo);
                    $userData = $this->controller->Auth->user();
                    $this->setUserExtraDetails($userData['id']); //set user extra details in session

                    $this->maintainUserLoginHistory(true);
                    $this->manageUserActivity($userInfo, SUCCESS);
                    $returnData['user'] = [
                        'firstName' => $userData['first_name'],
                        'lastName' => $userData['first_name'],
                        'firstName' => $userData['last_name'],
                        'userName' => $userData['username'],
                        'organization' => $userData['organization'],
                        'email' => $userData['email'],
                        'permissions' => []
                    ];
                    return $returnData;
                } else {
                    $this->manageUserActivity(['username' => $params["username"]], FAILED);
                    $this->doRemoteRedirect($loginUrl);
                }
            } else {
                $this->doRemoteRedirect($loginUrl);
            }
        }
    }

    /*
     * Function : to do logout from portal
     * @Params : none
     * Remark : Feature under discussion, If user click on logout button,
     * 1st - all session will be destroyed
     * 2nd - Portal's logout api will be called 
     * Once logout properly,  user will get redirected on portal (by using /src/AppController :: onSSOFilter(), this i called in beforeFilter)
     */

    public function doRemoteLogout($msg = "") {
        $ssoLogoutAPIURL = Configure::read('AUTHENTICATION.SSO_LOGOUT_URL');
        try {
            $httpResponse = $this->http->post($ssoLogoutAPIURL, [
                'TOKENIDENTIFIER' => isset($_COOKIE["PHPSESSID"]) ? $_COOKIE["PHPSESSID"] : "",
                    ]
            );
            if ($httpResponse->code != 200) {
                return FALSE;
            }
            if (isset($httpResponse->body) && $httpResponse->body != "") {
                if ($this->controller->Auth->logout()) {
                    $this->manageUserActivity('', '', true); //update user activity
                    //@$this->request->session()->destroy();
                    @session_unset();
                    unset($_COOKIE['DFAMON_SSN_ID']);
                    @setcookie('DFAMON_SSN_ID', null, -1, '/');
                    return TRUE;
                }
            }
        } catch (Exception $err) {
            return FALSE;
        }
    }

    /*
     * Function to do redirect on portal if any error encounter while authentication
     * @Params : Under discussion, Need to redirect on portal,
     * If any error occured during SSO authentication from portal,
     * Frontend team will have to work on this,
     */

    public function doRemoteRedirect($logoutUrl = "") {
        if (!empty($logoutUrl)) {
            if ($this->controller->Auth->logout()) {
                @session_unset();
            }
            return TRUE;
        }
    }

    /**
     * function to decrypt token inmformation
     */
    public function decryptToken($tokenKey = '') {
        $tokenData = '';
        if (!empty($tokenKey)) {
            $tokenData = base64_decode($tokenKey);
        }

        return $tokenData;
    }

    /**
     * function to parse token inmformation
     */
    public function parseToken($tokenData = '') {
        if (!empty($tokenData)) {
            preg_match_all("/\{(.*?)}/", $tokenData, $dataArray); //'/{(?P<name>\w+)}/'              
        }
        /*
          0 - id
          1 - email
          2 - username
          3 - fullname
          4 - custom user id (not in use)
          5 - token expiry time
         */
        return (isset($dataArray[1])) ? $dataArray[1] : [];
    }

    /*
     * Function Extract Token Info and prepare well defined array from it
     * @Params : string
     */

    public function extractTokenInfo($token = "") {
        $info = [];
        if (!empty($token)) {
            $token = str_replace(["{", "}"], ["", ""], $token);
            $tokenData = explode("~", $token);
            $info["id"] = $tokenData[0];
            $info["email"] = $tokenData[1];
            $info["username"] = $tokenData[2];
            $info["full_name"] = $tokenData[3];
            $info["session_id"] = $tokenData[4];
            $info["expire_time"] = $tokenData[5];
            $info["user_type"] = $tokenData[6];
        }
        return (count($tokenData) == 7) ? $info : [];
    }

    /**
     * method to maintain User Login History
     * @param boolean
     * @return boolean
     */
    public function maintainUserLoginHistory($atLogin = true) {
        $userId = $this->controller->Auth->User('id');
        if ($atLogin) {
            $UserLoginId = $this->MUserLoginHistory->accessUserLoginHistory(["user_id" => $userId, "UserLoginId" => ""]);
            $this->session->write('UserLoginId', $UserLoginId);
        } else {
            $UserLoginId = $this->session->read('UserLoginId');
            $this->MUserLoginHistory->accessUserLoginHistory(["user_id" => $userId, "UserLoginId" => $UserLoginId]);
        }
        return true;
    }

    /**
     * method to set global admin check and workspace details  in session of user 
     * @param type $userId
     */
    public function setUserExtraDetails($userId=''){
        $ISGLOBALADMIN =false;

        if(!empty($userId)){
            $allUserWrkPsaceIds = $prepareWkSpace=[];

            $checkGlobalAdmin = AppServiceUtil:: checkGlobalAdminRole($userId); 
            if($checkGlobalAdmin>0){
                $ISGLOBALADMIN =true;
            }
            
            $UserWorkspace = $this->UserWorkspace->getRecords([],['UserWorkspace.user_id'=>$userId],'all',['contain'=>'Workspace']);
            //pr($UserWorkspace);die;
            if(!empty($UserWorkspace)){
                foreach($UserWorkspace as $index=>$value){

                    $wpdata = $value['workspace'];
                    $prepareWkSpace[] = ['id' => $wpdata['id'], 'portal_url' => $wpdata['portal_url'], 'is_global' => $wpdata['is_global'], 'workspace_caption' => $wpdata['workspace_caption']];
                    $allUserWrkPsaceIds[] = $wpdata['id'];
                    
                }
           
            }
            
            }
            
            return ['prepareWkSpace'=>$prepareWkSpace ,'ISGLOBALADMIN'=>$ISGLOBALADMIN,'WorkspaceIds'=>$allUserWrkPsaceIds];

    }

}
