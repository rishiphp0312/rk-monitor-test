<?php
namespace ApiService\Controller;
use ApiService\Controller\AppController;
use Cake\Core\Configure;
use Cake\Network\Exception\NotFoundException;
use Cake\View\Exception\MissingTemplateException;
use Cake\Event\Event;
/*
* Services Controller to handle api request
*/
class ServicesController extends AppController {
    public $name = 'Services';
    public $components = ['Auth','ApiService.Service','User.UserCommon','Security.Security','Translation.Translation', 'User.RoleCommon', 'Tag.TagCommon', 'Tag.TagItemCommon'];
    /**
    * @param array $config 
    */
    public function initialize() {
        parent::initialize();       
        $this->session = $this->request->session(); 
        $this->excludedAuthenticateActions = Configure::read('ExcludeAuthenticateActions');
        $this->excludedAuthoriseActions = Configure::read('ExcludeAuthoriseActions');  
		$this->excludeActionsSettings = Configure::read('excludeActionsSettings');  
    }
    public function beforeFilter(Event $event) {
        parent::beforeFilter($event);       
        $this->Auth->allow();
    }
    /**
     * queryService method
     * Used as entrypoint to fetch api response data
     * @param int/String $actionCode i.e permission code or custom action (i.e code for getFrameworks,getFrameworkLevels etc)
     * Function to process apiservice request and return appropriate response
    */
     public function queryService($actionCode){   
        $this->autoRender = FALSE;
        $responseDetail = [];		
		
        // Check if requested action is allowed without login
		if(!empty($this->excludeActionsSettings[$actionCode])){

			$permDetails = $this->excludeActionsSettings[$actionCode];
			$pluginName = $permDetails['plugin_name'];
			$componentName = $permDetails['component_name'];
			$methodName = $permDetails['action_name']; 
			//$returnData = $this->processCustomAction($actionCode);
			$returnData = $this->Service->serviceInterface($pluginName,$componentName,$methodName);  
		}
		// Check if Authentication is not required for requested action
		else{
			$isUserLoggedIn =  $userId = 1;
			
			// Check if user is logged in or not
			//$isUserLoggedIn =  $this->UserCommon->checkUserLoggedIn();
			if(!$isUserLoggedIn){
				//Return unauthenticated header
				$this->Service->sendResponseHeader(401);
				exit;
			}
			
			// Get user assigned role
			//$userId = $this->Auth->User('id');
			$userRoles = $this->Security->getUserAssociatedRole($userId);		
			
			// If user role found
			if(!empty($userRoles)){
				$roleId = array_keys($userRoles);
				//echo $actionCode;pr($roleId);//exit;
				$selActionPermissionDetails = $this->Security->getPermissionDetailByActionNumber($actionCode, $roleId);
				//echo $actionCode;pr($selActionPermissionDetails);exit;
				
				// If permission detail record found
				if(!empty($selActionPermissionDetails) && !empty($selActionPermissionDetails['permission_detail'])){
					$params = $this->request->data;		
					
					//GET Permission details and process reques
					$permDetails = $selActionPermissionDetails['permission_detail'];
					
					$pluginName = $permDetails['plugin_name'];
					$componentName = $permDetails['component_name'];
					$methodName = $permDetails['action_name']; 
					//pr($params);exit;
					
					$returnData = $this->Service->serviceInterface($pluginName,$componentName,$methodName, $params);   
					//pr($returnData);exit;
				}
				// If permission detail record not found
				else{	
					$returnData = ['hasError'=>1, 'err' => ['errCode'=>'PERMISSION_NOT_FOUND']];
				}
			}
			// If user role not found
			else{		
				$returnData = ['hasError'=>1, 'err' => ['errCode'=>'USER_ROLE_NOT_FOUND']];
			}
		}

		//Process the returned response
		if(isset($returnData['hasError'])){

			//Check if any response header code is assigned in return result
			if(isset($returnData['httpCode'])){
				$this->Service->sendResponseHeader(403);
				exit;
			}
			$responseDetail['status'] = false;
			//$responseDetail['err'] = isset($returnData['err']) ? $returnData['err'] : []; 
			if(!empty($returnData['err']))
				$responseDetail = array_merge($responseDetail, $returnData['err']); 
			
		}else{
			$responseDetail['status'] = true;
			$responseDetail['data'] = $returnData;
		}
		
		return $this->Service->buildServiceResponse($responseDetail);		
    }
   /**
     * processCustomAction method
     * Used to process extra action fetch api response data
     * @param int/String $actionCode i.e permission code or custom action (i.e code for getFrameworks,getFrameworkLevels etc)
     * Function to process apiservice request and return appropriate response
    */
    public function processCustomAction($action)
    {
        switch($action) {
            case 'login':
                $returnData = $this->Service->serviceInterface('User','UserCommon','processFormLogin');              
            break;                                      
            default:
            break;
        }
    }
    
}
