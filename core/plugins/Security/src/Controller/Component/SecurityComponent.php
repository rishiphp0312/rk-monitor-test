<?php
namespace Security\Controller\Component;
use Cake\Controller\Component;
use Cake\ORM\TableRegistry;
use Cake\Core\Configure;
use Cake\I18n\Time;
use AppServiceUtil\Model\Table\UtilTableTrait;

/**
 * Area Component
 */
class SecurityComponent extends Component {   
    public $components = ['Auth'];
    public $userTableObj = NULL;
    public $roleTableObj = NULL;
    public $permissionTableObj = NULL;
    public $rolePermissionsTblObj = NULL;

    public function initialize(array $config) {
        parent::initialize($config);
        $this->session = $this->request->session();  

        $this->userTblObj = TableRegistry::get('User.User');
		$this->userRolesTblObj = TableRegistry::get('User.UserRoles');
        $this->rolesTblObj = TableRegistry::get('Security.Roles');
		$this->rolePermissionsTblObj = TableRegistry::get('Security.RolePermissions'); 
        
		$this->modulesTblObj = TableRegistry::get('Security.Modules');
		$this->permissionsTblObj = TableRegistry::get('Security.Permissions');
		$this->permissionDetailsTblObj = TableRegistry::get('Security.PermissionDetails');
    }

	/*
    * Function to get Permission table row
    */
    public function getUserAssociatedRole($userId){
		
		$roleDataArr = [];
		/*
		// Approach 1: Make query from user role table
		$query = $this->userRolesTblObj->find('all', [	
			'fields' => ['role_id', 'user_id'],
			'conditions' => ['UserRoles.user_id'=>$userId],			
		]);
		$query->hydrate(false);
		$query->contain(['Roles'=> function ($q) {
									return $q->select(['role_name','workspace_id', 'is_system_role'])
										->where(['is_active' => 1, 'is_deleted'=>0]);
								}
					]);
		$results = $query->all();	

		// Once we have a result set we can get all the rows
		if(!empty($results)) {			
			$results = $results->toArray();
			foreach($results as $result){
				$roleDataArr[$result['role_id']] = $result['role']['role_name'];
			}
        }
		echo "<br>";pr($results);exit;*/

		// Approach 2: Make query from user role table
		$query = $this->userRolesTblObj->find('list', [
			'keyField' => 'role_id',
			'valueField' => 'role.role_name',			
			'conditions' => ['UserRoles.user_id'=>$userId],		
		])->contain(['Roles'=> function ($q) {
									return $q->where(['is_active' => 1, 'is_deleted'=>0]);
								}
					]);		
		$roleDataArr = $query->toArray();
		return $roleDataArr;		
	}

	
    /*
    * Function to get Permission table row
    */
    public function getPermissionDetailByActionNumber($actionNumber, $roleId){
		
		// In a controller or table method.
		$query = $this->rolePermissionsTblObj->find('all', [
			'conditions' => ['RolePermissions.role_id IN '=>$roleId],			
		]);
		/*$query->matching('PermissionDetails', function ($q) use ($actionNumber) {
			return $q->where(['action_number' => $actionNumber]);
		});*/

		$query->contain(['PermissionDetails' => function ($q) use ($actionNumber) {
													return $q->where(['action_number' => $actionNumber]);
												}
		]);

		$results = $query->first();
		//debug($query, true) ;//exit;
        if(!empty($results)) {
			// Once we have a result set we can get all the rows
			$results = $results->toArray();
        }

        return $results;
    }
}
