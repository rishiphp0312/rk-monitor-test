<?php

namespace User\Model\Table;

use ArrayObject;
use Cake\Event\Event;
use Cake\ORM\Table;
use Cake\ORM\Entity;
use User\Model\Entity\UserWorkspace;
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
 * UserWorkspace Model
 */
class UserWorkspaceTable extends Table {

    use UtilTableTrait;

    /**
     * Initialize method
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config) {
        $this->table('r_user_workspaces');
        $this->primaryKey('id');
        $this->addBehavior('Timestamp');
		$this->BelongsTo('Workspace', [
            'className' => 'User.Workspace',
            'foreignKey' => 'workspace_id',
            'joinType' => 'INNER'
        ]);
		
    }
	

    public static function defaultConnectionName() {
        return 'default'; //Set whatever required
    }


}
