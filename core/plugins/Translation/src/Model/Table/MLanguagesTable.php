<?php
namespace Translation\Model\Table;
use ArrayObject;
use Cake\Event\Event;
use Cake\ORM\Table;
use Cake\ORM\Entity;
use Translation\Model\Entity\MLanguage;
use Cake\Network\Session;
use Cake\Validation\Validator;
use Cake\ORM\RulesChecker;
use AppServiceUtil\Utility\AppServiceUtil;
use Cake\Network\Exception\BadRequestException;
use Cake\DataSource\ConnectionManager;
use AppServiceUtil\Model\Table\UtilTableTrait;
use Cake\I18n\Time;

/**
 * MLanguages Model
 *
 * @property \Cake\ORM\Association\BelongsTo $ModifiedUsers
 * @property \Cake\ORM\Association\BelongsTo $CreatedUsers
 */
class MLanguagesTable extends Table
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

        $this->table('m_languages');
        $this->displayField('name');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

    }

     /**
     * 
     * @return string
     */
    public static function defaultConnectionName(){
        return 'default'; //Set whatever required
    }
    
    /**
    *  method to update language
    * @param type $fieldsArray
    * @return int
    */
    public function updateLanguage($fieldsArray = []) {  
        $id = (isset($fieldsArray['id'])) ? $fieldsArray['id'] : NULL;
        //Create New Entity        
        $Language = $this->get($id);
        if($Language){
            //Update New Entity Object with data
            $Language = $this->patchEntity($Language, $fieldsArray); 
            $result = $this->save($Language);                             
                if ($result) {                        
                    return 1;
                } else {
                    return AppServiceUtil::errResponse('SERVER_ERROR');
                }
           
             
        }
        else{           
            return AppServiceUtil::errResponse('INVALID_REQUEST');
        }  
    } 

}
