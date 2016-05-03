<?php

namespace User\Model\Table;

use ArrayObject;
use Cake\Event\Event;
use Cake\ORM\Table;
use Cake\ORM\Entity;
use User\Model\Entity\User;
use Cake\ORM\TableRegistry;
use Cake\Network\Session;
use Cake\Validation\Validator;
use Cake\ORM\RulesChecker;
use Cake\Utility\Hash;
use AppServiceUtil\Utility\AppServiceUtil;
use Cake\Network\Exception\BadRequestException;
use Cake\DataSource\ConnectionManager;
use AppServiceUtil\Model\Table\UtilTableTrait;
use Cake\I18n\Time;

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
    public function initialize(array $config) {
        $this->table('m_users');
        $this->primaryKey('id');
        $this->addBehavior('Timestamp');
    }

    public static function defaultConnectionName() {
        return 'default'; //Set whatever required
    }

    /*
     * Validation Rule For New/Update User 
     */

    public function validationUser(Validator $validator) {
        return $validator
                        ->requirePresence('workspace_id', 'create', 'COUNTRY_ID_MSG')
                        ->requirePresence('email', 'create', 'EMAIL_BLANK_MSG')
                        ->add(
                                'email', ['unique' => [
                                'rule' => 'validateUnique',
                                'provider' => 'table',
                                'message' => 'Email already in use']
                            , ['rule' => 'email', 'message' => 'EMAIL_VALID_MSG']
                                ]
                        )
                        ->requirePresence('first_name', 'create', 'FIRSTNAME_BLANK_MSG')
                        ->add('first_name', 'alpha', [
                            'rule' => ['custom', '/^[a-zA-Z]+$/'],
                            'message' => 'FIRSTNAME_ALPHA_ONLY',
                        ])
                        ->requirePresence('last_name', 'create', 'LASTNAME_BLANK_MSG')
                        ->allowEmpty('last_name', TRUE)
                        ->add('last_name', 'alpha', ['rule' => ['custom', '/^[a-zA-Z]+$/'], 'message' => 'LASTNAME_ALPHA_ONLY',])
                        ->requirePresence('password', 'create', 'PASSWORD_BLANK_MSG')
                        ->notEmpty('password', 'PASSWORD_BLANK_MSG', 'create')
                        ->requirePresence('organization', 'create', 'ORG_MSG')
                        ->requirePresence('contact_number', 'create', 'CONTACT_MSG')
                        ->add('contact_number', [
                            'minLength' => [
                                'rule' => ['minLength', 8],
                                'message' => 'CONTACT_MINLENGTH_MSG'
                            ]
                        ])
                        ->requirePresence('un_agency', 'create', 'AGENCY_MSG');
    }

    /**
     * setListTypeKeyValuePairs method     *
     * @param array $fields The fields(keys/values) for the list.
     * @return void
     */
    /* public function setListTypeKeyValuePairs(array $fields) {
      $this->primaryKey($fields[0]);
      $this->displayField($fields[1]);
      } */
    /**
     * getRecords method
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @param array $fields The Fields to SELECT from the Query. {DEFAULT : empty}
     * @return void
     */
    /* public function getRecords(array $fields, array $conditions, $type = 'all', $extra = []) {       
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
      } */

    /**
     * deleteRecords method
     * @param array $conditions Fields to fetch. {DEFAULT : empty}
     * @return void
     */
    public function deleteRecords(array $conditions) {
        $result = $this->deleteAll($conditions);
        return $result;
    }

    /**
     * checkEmailAvailibilty method
     * @param string $email to check uniqueness
     * @param int $uid for update case
     * @return true/false
     */
    public function checkEmailAvailibilty($email = '', $uid = '') {
        $conditions = $fields = [];
        $fields = ['id'];
        $conditions = ['email' => $email];
        if (isset($uid) && !empty($uid)) {
            $extra['id !='] = $uid;
            $conditions = array_merge($conditions, $extra);
        }
        $nameexits = $this->getRecords($fields, $conditions);
        if (!empty($nameexits)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * checkUsernameAvailibilty method
     * @param string $username to check uniqueness
     * @param int $uid for update case
     * @return true/false
     */
    public function checkUsernameAvailibilty($username = '', $uid = '') {
        $conditions = $fields = [];
        $fields = ['id'];
        $conditions = ['username' => $username];
        if (isset($uid) && !empty($uid)) {
            $extra['id !='] = $uid;
            $conditions = array_merge($conditions, $extra);
        }
        $nameexits = $this->getRecords($fields, $conditions);
        if (!empty($nameexits)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * insertUser method
     * @param array $fieldsArray User details to insert like first name,last name etc.
     * @return user id if successfull Or error array if false
     */
    public function insertUser($fieldsArray = []) {
        //Create New Entity
        $User = $this->newEntity();
        //Patch New Entity Object with request data
        $User = $this->patchEntity($User, $fieldsArray, ['validate' => 'User']);
        if (!$User->errors()) {
            //Create new row and Save the Data
            $result = $this->save($User);
            if ($result) {
                return $result->id;
            } else {
                return AppServiceUtil::errResponse($User->errors());
            }
        } else {
            return AppServiceUtil::errResponse($User->errors());
        }
    }

    /**
     * updateUser method
     * @param array $fieldsArray User details to update like first name,last name etc.
     * @return true if successfull Or error array if false
     */
    public function updateUser($fieldsArray = [], $validateData = true) {
        if (empty($fieldsArray['id'])) {
            return AppServiceUtil::errResponse('USER_ID_MISSING');
        }
        $uid = $fieldsArray['id'];
        //Create New Entity        
        $User = $this->get($uid);
        $validateDataArr = [];
        if ($validateData === true) {
            $validateDataArr = ['validate' => 'User'];
        }
        if ($User) {
            //Update New Entity Object with data
            $User = $this->patchEntity($User, $fieldsArray, $validateDataArr);
            if (!$User->errors()) {
                $result = $this->save($User);
                if ($result) {
                    return 1;
                } else {
                    return AppServiceUtil::errResponse($User->errors());
                }
            } else {
                return AppServiceUtil::errResponse($User->errors());
            }
        } else {
            return AppServiceUtil::errResponse('INVALID_REQUEST');
        }
    }

    /*
     * getCount method
     * get total no of records 
     * array @conditions  The WHERE conditions for the Query. {DEFAULT : empty} 
     */
    /* public function  getCount($conditions=[]){
      return  $total =  $this->find()->where($conditions)->count();

      } */

    /**
     * 
     * @param type $fieldsArray
     * @return boolean
     */
    public function updateUserInfo($fieldsArray = []) {
        $User = $this->newEntity();

        $User = $this->patchEntity($User, $fieldsArray);
        if ($this->save($User)) {
            return true;
        } else {
            return false;
        }
    }

    /*
     * getUserDefaultRole method 
     * Function to get User default role
     * @param int $userId to fetch default role
     * @return int default role id OR false
     */

    public function getUserDefaultRole($userId) {
        if ($userId) {
            $query = $this->find('all', ['fields' => ['default_role_id']]);
            $results = $query->first()->toArray();
            return $results['default_role_id'];
        }
        return FALSE;
    }

    function getUsersByRole($roleId, $countryId) {
        
    }

    /* Function to get list of country admins
     * @param $notActive to fetch disabled users
     * @return array List of users
     */

    function getCountryAdmins($notActive = false) {
        // Bind with UserRoles
        $this->hasMany('UserRoles', [
            'className' => 'User.UserRoles',
            'foreignKey'=>'user_id',
            'conditions'=>['role_id'=>COUNTRY_ADMIN_ROLE_ID]
        ]);
        $returnArray = [];
        // If disabled users requested
        if ($notActive) {
            $active = 0;
        } else {
            $active = 1;
        }
        $conditions=['is_active' => $active];
        // Create query
        $query = $this->find('all', ['conditions' => $conditions])
                ->contain('UserRoles')
                ->matching('UserRoles');
        foreach ($query as $row) {
            array_push($returnArray, $row->toArray()); 
        }
        return $returnArray;
    }
    
    /*
    * getUserDetails method 
    * Function to get User Details
    * @param array $params to fetch data
    * @return array
    */    
    public function getUserDetails($params=[]) {
        $conditions = !empty($params['conditions']) ? $params['conditions'] : [];
        $fields = (!empty($params['fields'])) ? $params['fields'] : [];
        $query = $this->find('all', ['conditions'=>$conditions])
                    ->select($fields);
        $query->hydrate(false);
        return $query->first();
    }    
    
	/***
		Function : createUserIfNotExists
		Created On : 05-Feb-2016
		Purpose : Create User If Not Existsin
		Return : Boolean
	**/
	public function createUserIfNotExists($userInfo=[]) {
		$passwordToUser = 'ANYRANDOMSTRING95561498375';
		$response = [];
		$response['status'] = false;
		$response['isNew'] = false;
		if(!empty($userInfo)) {
			$isExists = $this->find('all')
					->where([$this->aliasField('username')=>$userInfo["username"]])
					->count();
			if ($isExists) {
				$response['status'] = true;
			} else {
				$fullName = @explode("^", $userInfo["full_name"]);
				$dataToInsert = [
								 "username" => $userInfo["username"],
								 "email" => $userInfo["email"],
								 "first_name" => @$fullName[0],
								 "last_name" => @$fullName[1],
								];
				$userEntity = $this->newEntity();
				$userEntity = $this->patchEntity($userEntity, $dataToInsert);
				$userEntity->created_user_id = 1;
				$userEntity->created = date ("Y-m-d H:i:s");
				$userEntity->password = $passwordToUser;
				if ($this->save($userEntity)) {
					$response['status'] = true;
					$response['isNew'] = true;
				} else {
					$response['status'] = false;
				}
				$response['status'] = true;
			}
		}
		return $response;
	}        

}
