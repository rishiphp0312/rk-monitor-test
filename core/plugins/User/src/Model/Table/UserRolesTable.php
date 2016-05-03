<?php

namespace User\Model\Table;

use Cake\ORM\Table;
use Cake\Network\Session;
use Cake\Validation\Validator;
use Cake\ORM\RulesChecker;
use AppServiceUtil\Model\Table\UtilTableTrait;
use AppServiceUtil\Utility\AppServiceUtil;

/**
 * User Role Model
 */
class UserRolesTable extends Table {

    use UtilTableTrait;

    /**
     * Initialize method
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config) {
        $this->table('r_user_role');
        $this->primaryKey('id');

        $this->BelongsTo('Roles', [
            'className' => 'Security.Roles',
            'foreignKey' => 'role_id',
            'joinType' => 'INNER'
        ]);
		
		
	    $this->BelongsTo('MRoles', [
            'className' => 'User.Role',
            'foreignKey' => 'role_id',
            'joinType' => 'INNER',
			//'conditions'=>[]
        ]);
        $this->addBehavior('Timestamp');
    }
    
    /*
     * Validation Rule For New/Update UserRole 
     */

    public function validationUserRoles(Validator $validator) {
        return $validator
                        ->requirePresence('role_id', 'create', 'ROLE_BLANK_MSG')
                        ->requirePresence('user_id', 'create', 'USR_BLANK_MSG');                       
    }

    /**
     * Get user's roles
     * @param $user id.
     * @return role id 
     */
    public function getRole($userId) {
        if ($userId) {
            $returnArray=[];
            $query = $this->find('all', [
                'fields' => ['role_id'],
                'conditions' => ['user_id' => $userId]
            ]);
            foreach($query as $row){
                if(!empty($row->role_id)){
                    array_push($returnArray,$row->role_id);
                }
            }
        }
        return $returnArray;
    }
    /**
     * insertUserRole method
     * @param array $posData data send by user.
     * @return userRole id if successfull Or error array if false
     */
    public function addUserRole($posData) {
        //Create New Entity
        $userRole = $this->newEntity();
        //Patch New Entity Object with request data
        $userRole = $this->patchEntity($userRole, $posData, ['validate' => 'UserRoles']);
        if (!$userRole->errors()) {
            //Create new row and Save the Data
            $result = $this->save($userRole);
            if ($result) {
                return $result->id;
            } else {
                return AppServiceUtil::errResponse($userRole->errors());
            }
        } else {
            return AppServiceUtil::errResponse($userRole->errors());
        }
    }
}