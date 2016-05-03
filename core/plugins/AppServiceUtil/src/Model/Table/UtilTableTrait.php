<?php

namespace AppServiceUtil\Model\Table;

/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         3.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
use Cake\Datasource\Exception\MissingModelException;
use InvalidArgumentException;

/**
 * Provides some common functionality to use in every table class
 *
 */
trait UtilTableTrait {
    /*
     * Function to get find count in every table class whichever uses it
     * getCount method
     * @params conditions
     * return true/false
     */

    public function getCount($conditions = [], $extra = []) {
        $options = [];

        if (!empty($conditions))
            $options['conditions'] = $conditions;
        if (isset($extra['contain']) && !empty($extra['contain'])) {
            $options['contain'] = $extra['contain'];
        }



        if (isset($extra['debug']) && $extra['debug'] == true) {
            debug($query);
            exit;
        }

        $query = $this->find('all', $options);
        $results = $query->hydrate(false)->count();
        return $results;
    }

    /**
     * getFirst method
     * @param array $fields The Fields to SELECT from the Query. {DEFAULT : empty}
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @return void
     */
    public function getFirst(array $fields, array $conditions, $extra = []) {
        $options = [];
        if (!empty($fields)) {
            $options['fields'] = $fields;
        }
        if (!empty($conditions)) {
            $options['conditions'] = $conditions;
        }
        if (!empty($extra['contain'])) {
            $options['contain'] = $extra['contain'];
        }
        $query = $this->find('all', $options);
        // and return the result set.
        $results = $query->first();
        if (!empty($results)) {
            // Once we have a result set we can get all the rows
            $results = $results->toArray();
        }
        return $results;
    }

    /**
     * setListTypeKeyValuePairs method     *
     * @param array $fields The fields(keys/values) for the list.
     * @return void
     */
    public function setListTypeKeyValuePairs(array $fields) {
        $this->primaryKey($fields[0]);
        $this->displayField($fields[1]);
    }

    /**
     * getList method
     * Function used to populate list data key value pair
     * @param array $fields The Fields to SELECT from the Query. {DEFAULT : empty}
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @return void
     */
    public function getList(array $fields, array $conditions) {
        $options = [];
        if (!empty($fields)) {
            $options['fields'] = $fields;
        }
        if (!empty($conditions)) {
            $options['conditions'] = $conditions;
        }
        $this->setListTypeKeyValuePairs($fields);
        $query = $this->find('list', $options);
        // and return the result set.
        $results = $query->hydrate(false)->all();
        if (!empty($results)) {
            // Once we have a result set we can get all the rows
            $results = $results->toArray();
        }
        return $results;
    }

    /**
     * getRecords method
     * Function used to return all the matching data based on supplied conditions
     * @param array $fields The Fields to SELECT from the Query. {DEFAULT : empty}
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @return void
     */
    public function getRecords(array $fields, array $conditions, $type = 'all', $extra = []) {
        $options = [];
        if (!empty($fields))
            $options['fields'] = $fields;
        if (!empty($conditions))
            $options['conditions'] = $conditions;
        if (isset($extra['contain']) && !empty($extra['contain'])) {
            $options['contain'] = $extra['contain'];
        }
        if (isset($extra['locales']) && !empty($extra['locales'])) {
            $options['locales'] = $extra['locales'];
        }
        
        if ($type == 'list')
            $this->setListTypeKeyValuePairs($fields);
        $query = $this->find($type, $options);


        if (isset($extra['debug']) && $extra['debug'] == true) {
            debug($query);
            exit;
        }
        // and return the result set.
        if (isset($extra['first']) && $extra['first'] == true) {
            $results = $query->first();
        } else {
            $results = $query->hydrate(false)->all();
        }
        if (!empty($results)) {
            // Once we have a result set we can get all the rows
            $results = $results->toArray();
        }
        return $results;
    }

}
