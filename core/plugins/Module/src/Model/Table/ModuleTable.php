<?php

namespace Module\Model\Table;

use ArrayObject;
use Cake\Event\Event;
use Cake\ORM\Table;
use Cake\ORM\Entity;
use Module\Model\Entity\Module;
use Cake\Network\Session;
use Cake\Validation\Validator;
use Cake\ORM\RulesChecker;
use AppServiceUtil\Utility\AppServiceUtil;
use Cake\Network\Exception\BadRequestException;
use Cake\DataSource\ConnectionManager;
use AppServiceUtil\Model\Table\UtilTableTrait;
use Cake\I18n\Time;
use Cake\I18n\I18n;


/**
 * Module Model
 */
class ModuleTable extends Table {

    use UtilTableTrait;
	/**
     * Initialize method
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config) {
        $this->table('m_modules');
        $this->primaryKey('id');
        $this->addBehavior('Timestamp');

         $this->addBehavior('ShadowTranslate.ShadowTranslate',[
			 'translationTable' => 'm_modules_i18n'
        ]);
		
        $this->hasMany('RWorkspaceModule', [
            'className' => 'Module.RWorkspaceModule',
            'foreignKey' => 'module_id',
            'joinType' => 'INNER',
			//'conditions'=>['role_gid'=>'GA','is_system_role'=>SYSTEM_ROLE_YES]
        ]);
        
         
    }

    /**
     * 
     * @return string
     */
    public static function defaultConnectionName() {
        return 'default'; //Set whatever required
    }
    
    
    /**
    * Validation Rule For New/Update Module 
    */
    public function validationModule(Validator $validator) {
        /*return $validator->add('id', 'valid', ['rule' => 'numeric', 'message' => 'MODULE_ID_NUMERIC'])
                        ->allowEmpty('id', 'create')
                        ->requirePresence('module_caption', true,'MODULE_CAPTION_BLNK_MSG')
                        ->notEmpty('module_caption', 'MODULE_CAPTION_BLNK_MSG', false)
                        ->add('module_caption', 'alpha', [
                            'rule' => ['custom', '/^[a-zA-Z ]+$/'],
                            'message' => 'MODULE_CAPTION_ALPHA_ONLY',
                        ]);
         */
    }
    
    
    /**
     * insertModule method
     * @param array $fieldsArray Module details to insert like module name,etc 
     * @return module id if successfull Or error array if false
     */
    public function insertModule($fieldsArray = [],$allLanguages=[]) {
        if (!empty($fieldsArray['id'])) {
            return AppServiceUtil::errResponse('INVALID_REQUEST');
        }
        //Create New Entity
        $Module = $this->newEntity();
        //Patch New Entity Object with request data
        $Module = $this->patchEntity($Module, $fieldsArray,[] );
        if (!$Module->errors()) {       
            if(!empty($allLanguages)){
                foreach ($allLanguages as $langIndex => $langDetails) {
                    $Module->translation($langDetails['code'])->set($fieldsArray);                    
                }                
            }
            //Create new row and Save the Data
            $result = $this->save($Module);
            if ($result) {
                return $result->id;
            } else {
                return AppServiceUtil::errResponse('SERVER_ERROR');
            }
        } else {
               return  AppServiceUtil::errResponse($Module->errors());
            
        }
    }
    
     /**
     * updateModule method
     * @param array $fieldsArray module details to update like  module name,etc c
     * @return true if successfull Or error array if false
     * @validateRole default will be true if false no validation will apply 
     */
    public function updateModule($fieldsArray = [], $validateRole = true) {
        if (empty($fieldsArray['id'])) {
            return AppServiceUtil::errResponse('MODULE_ID_MISSING');
        }
        $validateArray = [];
        $mid = $fieldsArray['id']; //module id 
        /*if ($validateRole == true) {
            $validateArray = ['validate' => 'Module'];
        }*/
		//pr($validateArray);
        //Create New Entity        
        $Module = $this->get($mid);
        if ($Module) {
            //Update New Entity Object with data			
			//$Module->dirty('module_caption', true);
			//pr($fieldsArray);
            $Module = $this->patchEntity($Module, $fieldsArray, $validateArray);
			if (!$Module->errors()) {
                $result = $this->save($Module);
                if ($result) {
                     return $result->id;
                } else {
                    return AppServiceUtil::errResponse('SERVER_ERROR');
                }
            } else {
                return AppServiceUtil::errResponse($Module->errors());
            }
        } else {
            return AppServiceUtil::errResponse('INVALID_REQUEST');
        }
    }

   
}
