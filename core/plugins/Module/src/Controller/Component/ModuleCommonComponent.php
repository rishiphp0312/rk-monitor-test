<?php

namespace Module\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;
use Cake\I18n\Time;
use AppServiceUtil\Utility\AppServiceUtil;
use Cake\View\View;
use Cake\I18n\I18n;
use Cake\Auth\Storage;

/**
 * Role common Component used to perform user related activities
 */
class ModuleCommonComponent extends Component {

    public $modTblObj = NULL;
    public $controller = '';
    public $components = ['AppServiceUtil.UtilCommon', 'Auth', 'Security.Security'];
    public $loggedInUserWorkspaceIdFun = '';

    /*
     * Initialize function to setup some initial valrs and call method to do intial function
     * @access public
     * @params $config array to populate initialize time config vars to user component
     */

    public function initialize(array $config) {
        parent::initialize($config);
        $this->session = $this->request->session();
        $this->modTblObj = TableRegistry::get('Module.Module');
        $this->RWorkspaceModuleObj = TableRegistry::get('Module.RWorkspaceModule');
        $this->controller = $this->_registry->getController();
        $this->currentSelectedLang = $this->controller->getAppCurrentLanguage();
        $this->loggedInuserId = $this->Auth->User('id');
        $this->loggedInUserWorkspaceId = $this->Auth->User('workspace_id');
        // $this->loggedInUserWorkspaceIdFinal = AppServiceUtil::getUserWorkspaceId($this->loggedInuserId);
        $this->loggedInUserWorkspaceIdFinal = 2;
    }

    

    /**
     * method to update manadatory or caption value on basis of service status 
     * @param type $serviceStatus default false means only mandatory 
     * @param type $postedData posted data 
     */
    public function editModuleCaption($serviceStatus=false,$postedData=[]) {
        if (!empty($postedData)) {
            if (!empty($this->currentSelectedLang)) {
                $this->modTblObj->locale($this->currentSelectedLang); // specific locale    
            }
            $modListAdmin = []; //store module ids 
			$checkGlobalAdmin = $this->Auth->User('ISGLOBALADMIN');
			if($checkGlobalAdmin === true){
				$this->loggedInUserWorkspaceIdFinal = GLOBAL_ADMIN_WORKSPACE_ID;            	
			}else{				
				$modListAdmin =$this->RWorkspaceModuleObj->getList(['module_id','module_id'],['workspace_id' =>GLOBAL_ADMIN_WORKSPACE_ID]);
				$this->loggedInUserWorkspaceIdFinal = 2;
				$UserAllWorkspaceIdsSes = $this->Auth->User('WorkspaceIds');
				if (in_array($this->loggedInUserWorkspaceIdFinal, $UserAllWorkspaceIdsSes) == false) {
					return  AppServiceUtil::errResponse('INVALID_REQUEST');
				}	
			}
			
			$errCode = $this->customModuleValidation($postedData['sg'],$serviceStatus);
			if(isset($errCode['error'])){
				return  AppServiceUtil::errResponse($errCode['error']);
			}
			//delete exiisting combinations
			$this->RWorkspaceModuleObj->deleteAll(['workspace_id' =>$this->loggedInUserWorkspaceIdFinal]);//user of current workspace_id

            foreach ($postedData['sg'] as $index => $innerValue) {
                $isMandat = '';
                $dataValue = [];
                $isMandat = (isset($innerValue['isMandatory'])) ? trim($innerValue['isMandatory']) : false;
                $dataValue['created_user_id'] = $dataValue['modified_user_id'] = $this->loggedInuserId;
                $dataValue['id'] = trim($innerValue['id']);
                 //debug($serviceStatus);die;
				if($serviceStatus===true){
                    $dataValue['module_caption'] = (isset($innerValue['moduleCaption'])) ? trim($innerValue['moduleCaption']) : '';
                    $dataValue['module_description'] = (isset($innerValue['moduleDescription'])) ? $innerValue['moduleDescription'] : '';
					//$this->modTblObj->newEntity();
					$this->modTblObj->updateModule($dataValue);
					
                }
                
                if (!empty($dataValue)) {
                    $impModData = [];
                   
                    $impModData = ['workspace_id' => $this->loggedInUserWorkspaceIdFinal, 'module_id' => $innerValue['id']];
					if($isMandat ===true || $isMandat == 'true')	
                    $this->manageImplementorModule($impModData,$modListAdmin); //manage module and area id relations 
                }
            }
        }
    }

    /**
     * method to manage relations btw module  and area
     * @param type $moduleIds
     */
    public function manageImplementorModule($impModData = [],$modListAdmin=[]) {
        if (!empty($impModData)) {
            $checkGlobalAdmin = $this->Auth->User('ISGLOBALADMIN');	
			$impModData = array_merge($impModData, ['created_user_id' => $this->loggedInuserId, 'modified_user_id' => $this->loggedInuserId]);
			if($checkGlobalAdmin===false  && (!empty($modListAdmin) &&  in_array($impModData['module_id'],$modListAdmin)==false ) || $checkGlobalAdmin === true)
            $this->RWorkspaceModuleObj->insertImplementModule($impModData);
        }
    }

    /**
     * method to get module details 
     * @return type
     */
    public function getModuleDetails() {
        $data = [];
        $moduleId = (isset($this->request->data['moduleId'])) ? $this->request->data['moduleId'] : '';

        if (!empty($moduleId)) {
            $data['Module'] = $this->modTblObj->getRecords([], ['id' => $moduleId]);
        } else {
            return AppServiceUtil::errResponse('MODULE_ID_BLANK_MSG');
        }
        return $data;
    }

    /**
     * Method to get role and module association
     * @param 
     * @return association array
     */
    public function getRoleModuleRel() {
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

    /**
     * method to get module list for specific users 
     * @return type
     */
    public function getModuleCaption() {
        $enabledStatus = false;
        $prepareData = $conditions = $prepareData = $data = [];
        $checkGlobalAdmin = $this->Auth->User('ISGLOBALADMIN');
        $UserAllWorkspaceIdsSes = $this->Auth->User('WorkspaceIds');
        $UserAllWorkspace = $this->Auth->User('Workspace');
        if ($checkGlobalAdmin === true) {
            // case of global admin logged in 
            $conditions = ['RWorkspaceModule.workspace_id' => GLOBAL_ADMIN_WORKSPACE_ID];
        } else {
            if (!empty($UserAllWorkspaceIdsSes)) {
                if (in_array($this->loggedInUserWorkspaceIdFinal, $UserAllWorkspaceIdsSes) == true) {
                    $conditions = ['RWorkspaceModule.workspace_id IN ' => [$this->loggedInUserWorkspaceIdFinal,GLOBAL_ADMIN_WORKSPACE_ID]];
                } else {
                    return AppServiceUtil::errResponse('INVALID_REQUEST');
                }
            } else {
                return AppServiceUtil::errResponse('INVALID_REQUEST');
            }
        }
        if (!empty($this->currentSelectedLang)) {
            $this->modTblObj->locale($this->currentSelectedLang); // specific locale    
        }
        $modData = $this->modTblObj->getRecords([], [], 'all');
        $RWorkspaceModuleData = $this->RWorkspaceModuleObj->getList(['module_id', 'module_id'], $conditions);
        if (!empty($modData)) {
            foreach ($modData as $index => $valueModule) {
                if (!empty($RWorkspaceModuleData))
                    $enabledStatus = (in_array($valueModule['id'], $RWorkspaceModuleData) == true) ? true : false;
                $prepareData[] = array_merge($valueModule, ['enabledStatus' => $enabledStatus]);
            }
        }

        return $prepareData;
    }

    /**
     * method to get module list for specific users 
     * @return type
     */
    public function getModuleList() {
        $prepareData = $data = [];

        $checkOtherservice = false; // if true means captions also allowed else only modules in the list
        $userRoles = $this->Security->getUserAssociatedRole($this->loggedInuserId);

        if (!empty($userRoles)) {
            //906
            $roleId = array_keys($userRoles);

            $actionCode = 905;
            $selActionPermissionDetails = $this->Security->getPermissionDetailByActionNumber($actionCode, $roleId);
            $permisDetailId = (isset($selActionPermissionDetails['permission_detail']['id'])) ? $selActionPermissionDetails['permission_detail']['id'] : '';
            if ($permisDetailId > 0)
                $checkOtherservice = true;
        }


        if ($checkOtherservice == false) {

             $data = $this->modTblObj->getRecords([], [], 'all');
        } else {

             $data =  $this->getModuleCaption();
        }
        return ['module'=>$data];
    }
    
    /**
     * method to check service and update as per role type
     * update mandatory in case of ca else ga can update caption also
     */
    public function editModule(){
        $checkOtherservice = false; // if true means captions also allowed else only modules in the list
        $userRoles = $this->Security->getUserAssociatedRole($this->loggedInuserId);
        $postedData =  $this->request->data();

        if (!empty($userRoles)) {
            //906
            $roleId = array_keys($userRoles);

            $actionCode = 906;
            $selActionPermissionDetails = $this->Security->getPermissionDetailByActionNumber($actionCode, $roleId);
            $permisDetailId = (isset($selActionPermissionDetails['permission_detail']['id'])) ? $selActionPermissionDetails['permission_detail']['id'] : '';
            if ($permisDetailId > 0)
                $checkOtherservice = true;
        }


		
        return $this->editModuleCaption($checkOtherservice,$postedData);
        

    }
	
	
	/**
	method to validate edit module
	*/
	public function customModuleValidation($data=[],$serviceStatus=false){
		foreach($data as $value){
					//pr($value['moduleCaption']);
			if($serviceStatus===true){
				
			if(!isset($value['moduleCaption']) || empty(trim($value['moduleCaption']))){
				return ['error'=>'MODULE_CAPTION_BLNK_MSG'];
			}
			if(count($value['moduleCaption'])>100){
				return ['error'=>'MODULE_CAPTION_MAX_LENGTH'];
			}
			if(!preg_match('/^[a-zA-Z ]+$/', $value['moduleCaption']))
			{
				return ['error'=>'MODULE_CAPTION_ALPHA_ONLY'];
			}
			}
			
			if(!isset($value['id']) || empty($value['id'])){
				return ['error'=>'MODULE_ID_BLNK_MSG'];
				
			}
			if(!ctype_digit($value['id']))
			{
				return ['error'=>'MODULE_ID_NUMERIC'];
			}
			
		}
	}

}
