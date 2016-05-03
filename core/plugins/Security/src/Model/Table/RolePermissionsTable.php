<?php

namespace Security\Model\Table;

use Cake\ORM\Table;
use Cake\Network\Session;
use Cake\Validation\Validator;
use Cake\ORM\RulesChecker;

/**
 * Roles Model
 */
class RolePermissionsTable extends Table {

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config) 
    {
        parent::initialize($config);
        $this->table('r_role_module_permissions');
        $this->primaryKey('id');
        $this->addBehavior('Timestamp');

		$this->BelongsTo('PermissionDetails', [
			 'className' => 'Security.PermissionDetails',	
			 'foreignKey' => 'permission_detail_id',
			 'joinType' =>'INNER'
		]);
         
    }

    public static function defaultConnectionName() {
        return 'default'; //Set whatever required
    }

    /* 
    * Function to get a role permissions list
    */
    public function checkRoleHasPermission($roleId,$permissionId){
       
        $query = $this->find('all',['conditions' => ['role_id' => $roleId,'module_id' => $permissionId]]);        
        $cnt = $query->count();
       
        if($cnt>0){
            return true;
        }
        else{
            return false;
        }

    }
    
   
}
