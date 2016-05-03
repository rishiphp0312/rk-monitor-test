<?php

namespace User\Controller;

use User\Controller;
use Cake\Core\Configure;
use Cake\Network\Exception\NotFoundException;
use Cake\View\Exception\MissingTemplateException;
use Cake\Event\Event;
use AppServiceUtil\Utility\AppServiceUtil;
/*
 * Users Controller
 */

class UsersController extends AppController {
     public $name = 'Users';
	 
	 public function initialize() {
        parent::initialize();
        $this->loadComponent('Flash');
        $this->loadComponent('Auth', [
            'loginRedirect' => [
                'plugin' => 'User',
                'controller' => 'Users',
                'action' => 'view'
            ],
            'logoutRedirect' => [
                'plugin' => 'User',
                'controller' => 'Users',
                'action' => 'login'
            ],
            'authenticate' => [
                'Form' => [
                    //'fields' => ['username' => 'username'],
                    'userModel' => 'User.User'
                ]
            ]
        ]);
        $this->loadComponent('User.UserCommon');
        $helpers = ['Html'];       
        $this->session = $this->request->session(); 
		
    }

    //services/serviceQuery
    public function beforeFilter(Event $event) {
        parent::beforeFilter($event);
        $this->Auth->allow(['logout', 'login', 'fetchUserDetails']);
    }

    /**
     * login method
     * Function is basically used for local user form based login functionality
     * @return JSON/boolean
     * @throws NotFoundException When the view file could not be found
     * 	or MissingViewException in debug mode.     
     */
    public function login() {
        $this->autoRender = FALSE;
        $error = NULL;
        if ($this->request->is('post')) {
            $login_res = $this->UserCommon->processFormLogin();
            if (!isset($login_res['hasError']) || $login_res['hasError'] !== true) {
                $this->redirect(array('plugin' => 'User', 'controller' => 'Users', 'action' => 'index'));
            } else {
                $error = "Error : " . $login_res['err']['errCode'];
            }
        }
        $this->set('error', $error);
        $this->render('login');
    }

    /**
     * logout method
     */
    public function logout() {
        $this->autoRender = FALSE;
        $returnData = array();
        $isLoggedOut = $this->UserCommon->logoutUser();
        if ($isLoggedOut) {
            $this->redirect(array('plugin' => 'User', 'controller' => 'Users', 'action' => 'login'));
        }
    }

    /**
    * index method
    * Users loginRedirect action 
    */
   public function index(){       
      // $this->autoRender = FALSE;       
	  //$this->layout = 'service';
	  $this->viewBuilder()->layout('service');
       $isLoggedIn = $this->UserCommon->checkUserLoggedIn();      
       if($isLoggedIn){
        //   $userDetails = $this->UserCommon->getUsers([],['id' => $isLoggedIn],'all',['first' => true]);
          // $this->set('userDetails', $userDetails);
       }
       else{           
           echo "Please login first!";
       }
   }   
   
   
    /**
    * index method
    * Users loginRedirect action 
    */
   public function changePassword(){       
      // $this->autoRender = FALSE;  
       //die('hua');
       
       $this->UserCommon->accountActivation();      
       die('hua');
       if($isLoggedIn){
           $userDetails = $this->UserCommon->getUsers([],['id' => $isLoggedIn],'all',['first' => true]);
           //$this->set('userDetails', $userDetails);
       }
       else{           
           echo "Please login first!";
       }
   }
   
    /**
    * fetchUserDetails method
    * Users get user details
    * Purpose : would be used for sso purpose, can be access without any permission
    */   
   public function fetchUserDetails($params=[]) {
	 if (!empty($this->request->data)
		  && $this->request->is('post')
		  && (in_array($this->request->scheme(), ['http', 'https']))
	 ) {
		  $this->request->trustProxy = false;
		  $response = $this->UserCommon->getUserInfo($this->request->data);
		  $response = json_encode($response);
		  $this->response->charset('UTF-8');
		  $this->response->body($response);
	 }
	 return $this->response;
   }
   
}
