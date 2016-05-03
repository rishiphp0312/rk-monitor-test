<?php

namespace Security\Model\Table;

use Cake\ORM\Table;
use Cake\Network\Session;
use Cake\Validation\Validator;
use Cake\ORM\RulesChecker;

/**
 * Roles Model
 */
class RolesTable extends Table {

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config) 
    {
        parent::initialize($config);
        $this->table('m_roles');
        $this->primaryKey('id');
        $this->addBehavior('Timestamp');
        $this->hasMany('RolePermissions', ['className' => 'Security.RolePermissions','foreignKey' => 'role_id', 'dependent' => true]);
    }

    public static function defaultConnectionName() {
        return 'default'; //Set whatever required
    }
    /* Role Validation Rule Set */
   public function validationDefault(Validator $validator)
    {
        return $validator
            ->requirePresence('role_title')
            ->add('role_title', [
				'ruleUnique' => [
					'rule' => 'validateUnique',
					'provider' => 'table',
				]
			])
            ->allowEmpty('role_desc');
            
    }

    /**
     * setListTypeKeyValuePairs method
     *
     * @param array $fields The fields(keys/values) for the list.
     * @return void
     */
    public function setListTypeKeyValuePairs(array $fields) {
        $this->primaryKey($fields[0]);
        $this->displayField($fields[1]);
    }

    /**
     * getRecords method
     *
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @param array $fields The Fields to SELECT from the Query. {DEFAULT : empty}
     * @return void
     */
    public function getRecords(array $fields, array $conditions, $type = 'all', $extra = []) {       
        $options = [];
        if (!empty($fields))
            $options['fields'] = $fields;
        if (!empty($conditions))
            $options['conditions'] = $conditions;
        
        if ($type == 'list')
            $this->setListTypeKeyValuePairs($fields);

        $query = $this->find($type, $options);
         
        if(isset($extra['debug']) && $extra['debug'] == true) {
            debug($query);exit;
        }
        
        // and return the result set.
        if(isset($extra['first']) && $extra['first'] == true) {
            $results = $query->first();
        } else {
            $results = $query->hydrate(false)->all();            
        }
        
        if(!empty($results)) {
            // Once we have a result set we can get all the rows
            $results = $results->toArray();
        }
       
        return $results;
    }


   
   
}
