<?php

namespace User\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;
use Cake\I18n\Time;
use AppServiceUtil\Utility\AppServiceUtil;
//use Cake\Network\Email\Email;
use Cake\I18n\I18n;
use Cake\Core\Configure;

/**
 * Role common Component used to perform user related activities
 */
class RoleCommonComponent extends Component {

    public $roleTblObj = NULL;
    public $controller = '';
    public $components = ['AppServiceUtil.UtilCommon', 'Auth'];

    /*
     * Initialize function to setup some initial valrs and call method to do intial function
     * @access public
     * @params $config array to populate initialize time config vars to user component
     */

    public function initialize(array $config) {
        parent::initialize($config);
        $this->session = $this->request->session();
        $this->RoleTblObj = TableRegistry::get('User.Role');
        $this->RoleTblObj1 = TableRegistry::get('User.Role');
        $this->RoleTblObj3 = TableRegistry::get('User.Role');
        $this->UserRoleTblObj = TableRegistry::get('User.UserRoles');
        $this->UsersTblObj = TableRegistry::get('User.User');
		$this->RWorkspaceModuleObj = TableRegistry::get('Module.RWorkspaceModule');
        $this->controller = $this->_registry->getController();
        $this->loggedInuserId = $this->Auth->User('id');
        $this->currentSelectedLang = $this->controller->getAppCurrentLanguage();
        $this->loggedUserAreaId = $this->Auth->User('workspace_id');
       //die;

      
    }

	   


    /**
     * method to edit the role 
     * 
     */
    public function editRole() {

        $postedData = $this->request->data; //posted information
        // Convert fields name
        $postedData = AppServiceUtil::backendFrontendFieldsMap('Roles', $postedData, false, array()); //map posted data 
        //System related info 
		$checkGlobalAdmin = $this->Auth->User('ISGLOBALADMIN');
		
		if($checkGlobalAdmin === true){
			//if global admin logged in 
			$this->loggedInUserWorkspaceIdFinal = GLOBAL_ADMIN_WORKSPACE_ID;
        
		}else{				
			
			$this->loggedInUserWorkspaceIdFinal = 2;
			$UserAllWorkspaceIdsSes = $this->Auth->User('WorkspaceIds');
			if (empty($UserAllWorkspaceIdsSes) || in_array($this->loggedInUserWorkspaceIdFinal, $UserAllWorkspaceIdsSes) == false) {
				return  AppServiceUtil::errResponse('INVALID_REQUEST');
			}
		}
		$postedData['workspace_id'] = $this->loggedInUserWorkspaceIdFinal;		
		$postedData['modified_user_id'] = $this->loggedInuserId;
        
        // Save Role details 
       
        if (!empty($this->currentSelectedLang)) {
            $this->RoleTblObj->locale($this->currentSelectedLang); // specific locale    
        }
        $roleId = $this->RoleTblObj->updateRole($postedData);

        return $roleId;
    }

    /**
     * method to delete  role 
     * deletion will be soft delete is_deleted will be 1 
	   @roleId as id of role whom user want to delete 
     */
    public function deleteRole() {
        $postedData = $this->request->data;
        if (isset($postedData['roleId']) && !empty($postedData['roleId'])) {
            $checkGlobalAdmin = $this->Auth->User('ISGLOBALADMIN');
			if($checkGlobalAdmin === true){
			$this->loggedInUserWorkspaceIdFinal = GLOBAL_ADMIN_WORKSPACE_ID;     
			
		}else{				
			$this->loggedInUserWorkspaceIdFinal = 2;
			$UserAllWorkspaceIdsSes = $this->Auth->User('WorkspaceIds');
			if (empty($UserAllWorkspaceIdsSes) || in_array($this->loggedInUserWorkspaceIdFinal, $UserAllWorkspaceIdsSes) == false) {
				return  AppServiceUtil::errResponse('INVALID_REQUEST');
			}
            
		}
			$postedData = ['id' => $postedData['roleId'], 'is_deleted' =>IS_DELETED_YES, 'modified_user_id' => $this->loggedInuserId,
			'workspace_id'=>$this->loggedInUserWorkspaceIdFinal];

            return $roleId = $this->RoleTblObj->updateRole($postedData, false);
        } else {
            return AppServiceUtil::errResponse('INVALID_REQUEST');
        }
    }

    /**
     * method to get role details 
     * @param type $fieldsArray will contain role id 
     */
    public function getRoleDetails() {
        $data = [];
        $roleId = (isset($this->request->data['id'])) ? $this->request->data['id'] : '';
        if (!empty($roleId)) {
            if (!empty($this->currentSelectedLang)) {
                $this->RoleTblObj->locale($this->currentSelectedLang); // specific locale    
            }

            $data['Roles'] = $this->RoleTblObj->getFirst([], ['Role.id' => $roleId]);
        } else {
            return AppServiceUtil::errResponse('ROLEID_BLANK_MSG');
        }
        return $data;
    }

    /**
     * method to get list of Roles 
     */
    public function listRoles() {
        
        $fields = $prepareData = $data = [];
        $conditions =['is_deleted' => IS_DELETED_NO,'is_system_role'=>SYSTEM_ROLE_NO];
		$checkGlobalAdmin = $this->Auth->User('ISGLOBALADMIN');
		if($checkGlobalAdmin === true){
			//$this->loggedInUserWorkspaceIdFinal = GLOBAL_ADMIN_WORKSPACE_ID;     
			//if global admin logged in 
             $conditions = array_merge($conditions,['workspace_id' =>GLOBAL_ADMIN_WORKSPACE_ID]);			
		}else{				
			$this->loggedInUserWorkspaceIdFinal = 2;
			$UserAllWorkspaceIdsSes = $this->Auth->User('WorkspaceIds');
			if (empty($UserAllWorkspaceIdsSes) || in_array($this->loggedInUserWorkspaceIdFinal, $UserAllWorkspaceIdsSes) == false) {
				return  AppServiceUtil::errResponse('INVALID_REQUEST');
			}
             $conditions = array_merge($conditions,['workspace_id IN '=>[$this->loggedInUserWorkspaceIdFinal,GLOBAL_ADMIN_WORKSPACE_ID]]);
			
		}
		
        if (!empty($this->currentSelectedLang)) {
            $this->RoleTblObj->locale($this->currentSelectedLang); // specific locale    
        }
        
        $data = $this->RoleTblObj->getRecords([],$conditions, 'all');
        if(!empty($data)){
            foreach($data as $index=>$value){
                $enabledRole = ($value['workspace_id']==$this->loggedInUserWorkspaceIdFinal)?true:false;
                $prepareData[] = array_merge($value,['enabledStatus'=>$enabledRole]);
            }
        }
        return $prepareData;
        
    }

    /**
     * method to check role name validity
     * @return boolean
     */
    public function validateRoleName($data = [], $allLanguages = []) {

        if (!empty($data['workspace_id'])) {
            $id = '';
            //$results = $this->RoleTblObj->getRecords([], [], 'translations', ['locales' => ['en']]);
			//$results = $this->RoleTblObj->find('translations', ['locales' => ['en', 'es']]);
				//pr($results);die;
            foreach ($allLanguages as $langCode) {
				//pr($langCode);die;
                if (!empty($langCode['code'])) {
                    $this->RoleTblObj3->locale($langCode['code']); // specific locale    
                }
                $roleName = (isset($data['role_name'])) ? $data['role_name'] : '';
                $conditions = ['workspace_id' => $data['workspace_id']];
                if (isset($data['id'])) {

                    $id = $data['id'];
                    $conditions = array_merge($conditions, ['Role.id !=' => $id]);
                }
                $results = $this->RoleTblObj3->getRecords([], $conditions, 'all');
                //$results = $this->RoleTblObj->getRecords([], $conditions, 'translations', ['locales' => [$langCode['code']]]);
				
               // pr($results);die;
				if (!empty($results)) {
                    foreach ($results as $index => $value) {
                        if (trim($roleName) == trim($value['role_name'])) {

                            return ['error' => 'ROLE_NAME_UNIQ'];
                        }
                    }
                }
            }
			
                if (!empty($this->currentSelectedLang)) {
                    //$this->RoleTblObj3->locale($this->currentSelectedLang); // specific locale    
                }
        }
        return true;
    }

    
    
    /**
     * method to return role gid 
     * @param type $roleName
     */
    public function getRoleGid($roleName =''){
        if(!empty($roleName)){
            $roleName = str_replace(' ', DELEM4,$roleName);
            $roleName = strtoupper($roleName);
        }
        return $roleName;
    }
    
    /**
     * method to add new role in all the languages 
     * 
     */
    public function addRole() {
        $postedData = $this->request->data; //posted information
        // Convert fields name
        $postedData = AppServiceUtil::backendFrontendFieldsMap('Roles', $postedData, false, array()); //map posted data 
		$checkGlobalAdmin = $this->Auth->User('ISGLOBALADMIN');
		if($checkGlobalAdmin === true){
			$this->loggedInUserWorkspaceIdFinal = GLOBAL_ADMIN_WORKSPACE_ID;            	
		}else{				
			$this->loggedInUserWorkspaceIdFinal = 2;
			$UserAllWorkspaceIdsSes = $this->Auth->User('WorkspaceIds');
			if (empty($UserAllWorkspaceIdsSes) || in_array($this->loggedInUserWorkspaceIdFinal, $UserAllWorkspaceIdsSes) == false) {
				return  AppServiceUtil::errResponse('INVALID_REQUEST');
			}	
		}
        //System related info 
        $postedData['workspace_id'] = $this->loggedInUserWorkspaceIdFinal;
        $postedData['created_user_id'] = $postedData['modified_user_id'] = $this->loggedInuserId;
        $postedData['role_gid'] = $this->getRoleGid($postedData['role_name']);//create gid of role 
        // Save Role details 
        
        $allLanguages = AppServiceUtil:: getAllLanguages();
		//$err = $this->validateRoleName($postedData, $allLanguages['languagesList']);
		//pr($err);die;
		//if (isset($err['error']))
          // return AppServiceUtil::errResponse($err['error']);
        $newRoleId =  $this->RoleTblObj->insertRole($postedData);
		if(!$newRoleId['hasError']){
			if (count($allLanguages['languagesList']) > 0) {

			foreach ($allLanguages['languagesList'] as $langIndex => $langDetails) {
                    I18n::locale($langDetails['code']);	  
                    $this->RoleTblObj1->id = $newRoleId; //set role id 
                    $RoleData =$this->RoleTblObj1->newEntity($postedData);
                    $RoleData->id = $newRoleId;
                    $this->RoleTblObj1->save($RoleData);
                 
            }
			}
			if (!empty($this->currentSelectedLang)) {

				$this->RoleTblObj->locale($this->currentSelectedLang);
			}
                    
		}else{
			return $newRoleId;
		}
    }
	
	

}
