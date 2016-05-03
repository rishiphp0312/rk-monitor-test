<?php

namespace User\Model\Table;

use ArrayObject;
use Cake\Event\Event;
use Cake\ORM\Table;
use Cake\ORM\Entity;
use User\Model\Entity\Role;
use Cake\Network\Session;
use Cake\Validation\Validator;
use Cake\ORM\RulesChecker;
use AppServiceUtil\Utility\AppServiceUtil;
use Cake\Network\Exception\BadRequestException;
use Cake\DataSource\ConnectionManager;
use AppServiceUtil\Model\Table\UtilTableTrait;
use Cake\I18n\Time;



/**
 * Role Model
 */
class RoleTable extends Table {

    use UtilTableTrait;
    
    /**
     * Initialize method
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config) {
        $this->table('m_roles');
        $this->primaryKey('id');
        $this->addBehavior('Timestamp');
        
        $this->addBehavior('ShadowTranslate.ShadowTranslate',[
			 'translationTable' => 'm_roles_i18n'
        ]);
        /*$this->addBehavior('Translate', [
            'fields' => ['role_description', 'role_name'],
            'translationTable' => 'm_roles_i18n'
        ]);*/
        
    }
    
    

    /**
     * 
     * @return string
     */
    public static function defaultConnectionName() {
        return 'default'; //Set whatever required
    }

    /**
    * Validation Rule For New/Update Role 
    */
    public function validationRole(Validator $validator) {
        return $validator->add('id', 'valid', ['rule' => 'numeric', 'message' => 'ROLEID_NUMERIC'])
                        ->allowEmpty('id', 'create')
                        ->requirePresence('workspace_id', true,'COUNTRY_ID_BLANK_MSG')
                        ->notEmpty('workspace_id', 'COUNTRY_ID_BLANK_MSG', false)						 
                        ->requirePresence('role_name', true,'ROLENAME_BLANK_MSG')
                        ->notEmpty('role_name', 'ROLENAME_BLANK_MSG', false)
                        ->add('role_name', 'alpha', [
                            'rule' => ['custom', '/^[a-zA-Z .]+$/'],
                            'message' => 'ROLENAME_ALPHA_ONLY',
                        ])->add('role_name', [
                            'maxLength' => [
                                'rule' => ['maxLength', 100],
                                'message' => 'ROLENAME_MINLENGTH_MSG'
                            ]
                        ])		;
    }
    

    /**
     * updateRole method
     * @param array $fieldsArray Role details to update like  name,country id  etc.
     * @return true if successfull Or error array if false
     * @validateRole default will be true if false no validation will apply 
     */
    public function updateRole($fieldsArray = [], $validateRole = true) {
        if (empty($fieldsArray['id'])) {
            return AppServiceUtil::errResponse('ROLE_ID_MISSING');
        }
        $validateArray = [];
        $uid = $fieldsArray['id'];
        if ($validateRole == true) {
            $validateArray = ['validate' => 'Role'];
        }
        //Create New Entity        
        $Role = $this->get($uid);
        if ($Role) {
            if($Role['is_system_role']==SYSTEM_ROLE_YES){
                  return AppServiceUtil::errResponse('SYSTEM_ROLE_NOT_ALLOWED');
				  
            }
			if($Role['workspace_id']!=$fieldsArray['workspace_id']){
				                  return AppServiceUtil::errResponse('INVALID_REQUEST');

			}
            //Update New Entity Object with data
            $Role = $this->patchEntity($Role, $fieldsArray, $validateArray);
            if (!$Role->errors()) {
                $result = $this->save($Role);
                if ($result) {
                     return $result->id;
                } else {
                    return AppServiceUtil::errResponse('SERVER_ERROR');
                }
            } else {
                return AppServiceUtil::errResponse($Role->errors());
            }
        } else {
            return AppServiceUtil::errResponse('INVALID_REQUEST');
        }
    }
    
    
    
    /**
     * insertRole method
     * @param array $fieldsArray Role details to insert like role name,etc 
     * @return role  id if successfull Or error array if false
     */
    public function insertRole($fieldsArray = [],$allLanguages=[]) {
        if (!empty($fieldsArray['id'])) {
            return AppServiceUtil::errResponse('INVALID_REQUEST');
        }
        //['validate' => 'Role'];
        //Create New Entity
        $Role = $this->newEntity();
        //Patch New Entity Object with request data
        $Role = $this->patchEntity($Role, $fieldsArray, ['validate' => 'Role']);
        if (!$Role->errors()) {          
           

            //Create new row and Save the Data
            $result = $this->save($Role);
            if ($result) {
                return $result->id;
            } else {
                return AppServiceUtil::errResponse('SERVER_ERROR');
            }
        } else {
               return  AppServiceUtil::errResponse($Role->errors());
            
        }
    }
    
    /**
     * Method to get role_id 
     * @param $area_id ,role_gid 
     * @return role  id if successfull Or error array if false
     */
    public function getRoleId($area_id,$roleGid) {
        
        $query=$this->find('all')
                ->where(['area_id'=>$area_id,'role_gid'=>$roleGid]);
        $data=$query->first();        
        $Role = $this->newEntity();
        //Patch New Entity Object with request data
        $Role = $this->patchEntity($Role, $fieldsArray, []);
        if (!$Role->errors()) {       
            /*if(!empty($allLanguages)){
                foreach ($allLanguages as $langIndex => $langDetails) {
                    $Role->translation($langDetails['code'])->set($fieldsArray);
                    
                }
                
            }*/        

            //Create new row and Save the Data
            $result = $this->save($Role);
            if ($result) {
                return $result->id;
            } else {
                return AppServiceUtil::errResponse('SERVER_ERROR');
            }
        } else {
               return  AppServiceUtil::errResponse($Role->errors());
            
        }
    }
}
