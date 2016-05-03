<?php

namespace SSO\Controller;

use User\Controller;
use Cake\Core\Configure;
use Cake\Network\Exception\NotFoundException;
use Cake\View\Exception\MissingTemplateException;
use Cake\Event\Event;

/*
 * Authenticates Controller
*/
class AuthenticatesController extends AppController 
                                implements iAuthenticatesInterface {

	 public $name = 'Authenticates';
	 public $allowedActions = [];
	 public $cLoginRedirect = [];
	 public $cLogoutRedirect = [];
	 public $session = NULL;
	 public function initialize() {
		 parent::initialize();
		 $this->allowedActions = ['logout', 'login'];
		 $this->cLoginRedirect = [
							  'plugin' => 'SSO',
							  'controller' => 'Authenticates',
							  'action' => 'index'
							  ];
		 $this->cLogoutRedirect = [
				 'plugin' => 'SSO',
				 'controller' => 'Authenticates',
				 'action' => 'login'
			 ];
		 $this->loadComponent('Flash');
		 $this->loadComponent('Auth', [
										'loginRedirect' => $this->cLoginRedirect, 
										'logoutRedirect' => $this->cLogoutRedirect,
										'authenticate' => ['Form' => ['userModel' => 'SSO.User']]
									]);
		 $this->loadComponent('SSO.AuthRequest');
		 $helpers = ['Html'];
		 $this->session = $this->request->session();
	 }

    public function beforeFilter(Event $event) {
        parent::beforeFilter($event);
        $this->Auth->allow($this->allowedActions);
		if (isset($this->request->params["action"])
			&& !in_array($this->request->params["action"], $this->allowedActions)
		  ) {
		  // If user not logged In
		  if (!$this->session->check('Auth.User.id'))
			$this->redirect($this->cLogoutRedirect);
		}
    }

    /**
     * Login method
     */
    public function login($params = []) {
        $this->viewBuilder()->layout('login_layout');
        $appConfigInfo = \SSO\Model\Table\UserTable::getAppConfigurations([
						 'conditions' => ['guid' => 'AUTH'],
						 'fields' => ['settings']
        ]);
        $ssoSettings = [];
        if (!empty($appConfigInfo['settings'])) {
            $ssoSettings = json_decode($appConfigInfo['settings'], true);
        }

        if (isset($ssoSettings['CURRENT_MODE']) 
               && $ssoSettings['CURRENT_MODE'] == 'INTERNAL') {
            // process internal login
            $this->AuthRequest->initiateInternalLogin();
        } else if ($ssoSettings['CURRENT_MODE'] 
               && $ssoSettings['CURRENT_MODE'] == 'REMOTE') {
            // process remote login
            $this->AuthRequest->initiateRemoteLogin();
        } else {
            
        }
    }

    public function refreshTokenIdentifier($params = []) {
        
    }

    public function fetchsession($params = []) {
        
    }

    public function logout() {
         if ($this->Auth->logout()) {
            $this->redirect($this->cLogoutRedirect);
        }
    }

    public function index() {
        echo __('Welcome User...<a href="logout">Logout</a>');
		pr($this->session->read('Auth.User'));
		die;
    }
    
}
