<?php
namespace SSO\Model\Table;
use ArrayObject;
use Cake\Event\Event;
use Cake\ORM\Table;
use Cake\ORM\Entity;
use User\Model\Entity\User;
use Cake\Network\Session;
use Cake\Validation\Validator;
use Cake\ORM\RulesChecker;
use Cake\Utility\Hash;
use AppServiceUtil\Utility\AppServiceUtil;
use Cake\Network\Exception\BadRequestException;
use Cake\DataSource\ConnectionManager;
use AppServiceUtil\Model\Table\UtilTableTrait;
use Cake\I18n\Time;
use Cake\ORM\TableRegistry;
/**
* User Model
*/
class UserTable extends Table {
    use UtilTableTrait;
    /**
    * Initialize method
    * @param array $config The configuration for the Table.
    * @return void
    */
    public function initialize(array $config) 
    {   
        $this->table('m_users');
        $this->primaryKey('id');
        $this->addBehavior('Timestamp');
    }
    public static function defaultConnectionName(){
        return 'default'; //Set whatever required
    }     
    /* 
    * Validation Rule For New/Update User 
    */
    public function validationUser(Validator $validator)
    {
    } 

    /*
    * getAppConfigurations method 
    * Function to get App Configurations settings
    * @param array $params to fetch data
    * @return array
    */    
    public static function getAppConfigurations($params=[]) {
        $data = $fields = $conditions = [];
        if (!empty($params['conditions']))
            $conditions = $params['conditions'];
            
        if (!empty($params['fields']))
            $fields = $params['fields'];
            
        $appConfigurationsObj = TableRegistry::get('app_configurations');
        $query = $appConfigurationsObj->find('all', ['conditions'=>$conditions])
                                        ->select($fields);
        $query->hydrate(false);
        $data = $query->first();
        return $data;
    }
    
}
