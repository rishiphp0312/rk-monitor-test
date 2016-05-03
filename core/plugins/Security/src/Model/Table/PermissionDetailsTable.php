<?php

namespace Security\Model\Table;
use Cake\ORM\Table;
use Cake\Network\Session;
use Cake\Validation\Validator;
use Cake\ORM\RulesChecker;
use AppServiceUtil\Model\Table\UtilTableTrait;
/**
 * Permissions Model
 */
class PermissionDetailsTable extends Table {
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
        $this->table('m_permission_details');
        $this->primaryKey('id');
        $this->addBehavior('Timestamp');
    }   
   
}
