<?php

namespace Module\Model\Table;

use ArrayObject;
use Cake\Event\Event;
use Cake\ORM\Table;
use Cake\ORM\Entity;
use Module\Model\Entity\RWorkspaceModule;
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
 * RWorkspaceModule Model
 */
class RWorkspaceModuleTable extends Table {

    use UtilTableTrait;
	/**
     * Initialize method
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config) {
        $this->table('r_workspace_module');
        $this->primaryKey('id');
        $this->addBehavior('Timestamp');
        $this->addBehavior('ShadowTranslate.ShadowTranslate',[
			 'translationTable' => 'm_modules_i18n'
        ]);
		
         $this->belongsTo('Module', [
            'className' => 'Module.Module',
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
     * insertImplementModule method
     * @param array $fieldsArray Module details with area of user 
     * @return boolean
     */
    public function insertImplementModule($fieldsArray = []) {
        if (!empty($fieldsArray['id'])) {
            return AppServiceUtil::errResponse('INVALID_REQUEST');
        }
        //Create New Entity
        $ImplementModule = $this->newEntity();
        //Patch New Entity Object with request data
        $ImplementModule = $this->patchEntity($ImplementModule, $fieldsArray,[] );
        //Create new row and Save the Data
        $result = $this->save($ImplementModule);
        if ($result) {
                return $result->id;
        } else {
                return AppServiceUtil::errResponse('SERVER_ERROR');
        }
        
    }
    
    
    
  

   
}
