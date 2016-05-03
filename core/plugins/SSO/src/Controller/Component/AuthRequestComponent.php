<?php

namespace SSO\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;
use Cake\I18n\Time;
use AppServiceUtil\Utility\AppServiceUtil;
use Cake\Network\Email\Email;
use Cake\Auth\DefaultPasswordHasher;
use Cake\View\View;
use Cake\Core\Configure;
/**
 * User common Component used to perform user related activities
 */
class AuthRequestComponent extends Component {

    public $UserTblObj = NULL;
    public $controller = '';
    public $components = [];

    /*
     * Initialize function to setup some initial valrs and call method to do intial function
     * @access public
     * @params $config array to populate initialize time config vars to user component
     */

    public function initialize(array $config) {
        parent::initialize($config);
        $this->session = $this->request->session();
        $this->UserTblObj = TableRegistry::get('SSO.User');
        $this->controller = $this->_registry->getController();

        // Do few auth setting before to login in system
        $this->controller->Auth->config('authenticate', [
            'Form' => [
                'userModel' => 'SSO.User',
                'fields' => ['username' => 'username', 'password' => 'password'],
                'scope' => ['User.is_active' => 1, 'User.is_deleted' => 0]
            ]
        ]);
        Configure::write('Session', [
            'defaults' => 'php',
            'ini' => [
                // Invalidate the cookie after x minutes without visiting
                'session.cookie_lifetime' => 10
            ]
        ]);        
    }

    public function initiateInternalLogin($params = []) {
        if (!empty($this->request->data) && $this->request->is('POST')) {
            $username = (isset($this->request->data['username']))
                        ? $this->request->data['username'] : '';
            $password = $this->request->data['password'];
            
            if (!empty($username) && !empty($password)) {
                $userInfo = $this->controller->Auth->identify();
                if (!empty($userInfo)) {
                    //$this->manageUserActivity($user,SUCCESS);
                    $this->controller->Auth->setUser($userInfo);
                    $userData = $this->controller->Auth->user();
                    $sessionId = $this->request->session()->id();
                    $currentDateTime = date("Y-m-d H:i:s");
                    $fullName = $userData['first_name'] . "^" . $userData['last_name'];
                    $tokenExpireTime = date("Y-m-d H:i:s", strtotime($currentDateTime . " +10 minutes"));
                    $userType = "INTERNAL";
                    $tokenKey = "{".$userData['id']."}~{".$userData['email']."}~{".$userData['username']."}~{".$fullName."}~{".$sessionId."}~{".$tokenExpireTime."}~{".$userType."}";
                    $tokenKey = $this->encrypt($tokenKey, "test-key");
                    echo $tokenKey;die;
                    $this->controller->redirect(['plugin'=>'SSO', 'controller'=>'Authenticates', 'action'=>'index']);
                } else {
                    //$this->manageUserActivity(['username'=>$_POST['username']],FAILED);
                    //return AppServiceUtil::errResponse('INVALID_LOGIN_CRED');
                }
            } else {
                 //return AppServiceUtil::errResponse('MISSING_PARAMETERS');
            }
        }
    }

    public function initiateRemoteLogin($params = []) {
        
    }  
    
    /**
     * Returns an encrypted & utf8-encoded
    */
    public function encrypt($pure_string, $encryption_key) {
        $pure_string = base64_encode($pure_string);
        //return urlencode($pure_string);
        return $pure_string;
    }
    
    /**
     * Returns decrypted original string
    */
    public function decrypt($encrypted_string, $encryption_key) {
        return base64_decode($encrypted_string);
    }	

}
