<?php
namespace Translation\Model\Table;
use ArrayObject;
use Cake\Event\Event;
use Cake\ORM\Table;
use Cake\ORM\Entity;
use Translation\Model\Entity\MTranslation;
use Cake\Network\Session;
use Cake\Validation\Validator;
use Cake\ORM\RulesChecker;
use AppServiceUtil\Utility\AppServiceUtil;
use Cake\Network\Exception\BadRequestException;
use Cake\DataSource\ConnectionManager;
use AppServiceUtil\Model\Table\UtilTableTrait;
use Cake\I18n\Time;
use Cake\Core\Configure;

/**
 * MTranslations Model
 */
class MTranslationsTable extends Table
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
        $this->table('m_translations');
        $this->displayField('id');
        $this->primaryKey('id');
        $this->addBehavior('Timestamp');
       
    }
    
    
    /* 
    * Validation Rule For New/Update Translations 
    */
    public function validationMTranslations(Validator $validator)
    {
        $defltLang =  Configure::read('DFLT_LANG_CODE');
        $validator->add('id', 'valid', ['rule' => 'numeric'])->allowEmpty('id', 'create')
              ->requirePresence('code',true,'CODE_BLANK_MSG')
                ->notEmpty('code', 'CODE_BLANK_MSG', false)->add(
                'code', ['unique' => [
                    'rule' => 'validateUnique', 
                    'provider' => 'table', 
                    'message' => 'CODE_NOT_UNIQ'],
                     
                ])->add('code', 'alpha', [
                      'rule' => ['custom','/^[a-zA-Z0-9-_]+$/'],
                      'message' => 'INVALID_CODE_CHARS',
                         ])
            ->requirePresence($defltLang, true,'ENG_BLANK_MSG')
            ->notEmpty($defltLang,'ENG_BLANK_MSG');
           

        return $validator;
       
    } 
    
    /**
     * 
     * @return string
     */
    public static function defaultConnectionName(){
        return 'default'; //Set whatever required
    }
    
    /**
     * method to add translation 
     * @param type $fieldsArray
     * @return type
     */
    public function insertTranslation($fieldsArray = []) {
        //Create New Entity
        $Translation = $this->newEntity();
        //Patch New Entity Object with request data
        $Translation = $this->patchEntity($Translation, $fieldsArray,['validate' => 'MTranslations']);   
        if(!$Translation->errors()){           
            //Create new row and Save the Data
            $result = $this->save($Translation);        
            if ($result) {
                return $result->id;
            } else {
                return AppServiceUtil::errResponse($Translation->errors());
            } 
        }
        else{               
            return AppServiceUtil::errResponse($Translation->errors());
        }     
    }
    
    
    /**
    *  method to update translation
    * @param type $fieldsArray
    * @return int
    */
    public function updateTranslation($fieldsArray = []) {  
        $id = (isset($fieldsArray['id'])) ? $fieldsArray['id'] : NULL;
        //Create New Entity        
        $Translation = $this->get($id);
        if($Translation){
            //Update New Entity Object with data
            $Translation = $this->patchEntity($Translation, $fieldsArray,['validate' => 'MTranslations']); 
            if(!$Translation->errors()){ 
                $result = $this->save($Translation);                             
                if ($result) {                        
                    return 1;
                } else {
                    return AppServiceUtil::errResponse($Translation->errors());
                }
           
            } 
            else{           
                return AppServiceUtil::errResponse($Translation->errors());
            }  
        }
        else{           
            return AppServiceUtil::errResponse('INVALID_REQUEST');
        }  
    } 

   

   
}
