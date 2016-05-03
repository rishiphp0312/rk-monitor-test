<?php
namespace Module\Model\Table;


use ArrayObject;
use Cake\Event\Event;
use Cake\ORM\Table;
use Cake\ORM\Entity;

use Module\Model\Entity\RImplementorModule;
use Cake\Network\Session;
use Cake\Validation\Validator;
use Cake\ORM\RulesChecker;
use AppServiceUtil\Utility\AppServiceUtil;
use Cake\Network\Exception\BadRequestException;
use Cake\DataSource\ConnectionManager;
use AppServiceUtil\Model\Table\UtilTableTrait;
use Cake\I18n\Time;

/**
 * RImplementorModule Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Modules
 * @property \Cake\ORM\Association\BelongsTo $Areas
 */
class RImplementorModuleTable extends Table
{

    use UtilTableTrait;
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->table('r_implementor_module');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

       
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
    /*public function validationModule(Validator $validator) {
        return $validator->add('id', 'valid', ['rule' => 'numeric', 'message' => 'MODULE_ID_NUMERIC'])
                        ->allowEmpty('id', 'create')
                        ->requirePresence('is_mandatory', true,'MANDATORY_VAL_BLANK_MSG')
                        ->notEmpty('is_mandatory', 'MANDATORY_VAL_BLANK_MSG', false)
                        ->requirePresence('module_caption', true,'MODULE_CAPTION_BLNK_MSG')
                        ->notEmpty('module_caption', 'MODULE_CAPTION_BLNK_MSG', false)
                        ->add('module_caption', 'alpha', [
                            'rule' => ['custom', '/^[a-zA-Z ]+$/'],
                            'message' => 'MODULE_CAPTION_ALPHA_ONLY',
                        ])
            
            ;
         
    }
    */
    
    
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
