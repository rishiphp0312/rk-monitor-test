<?php

namespace WorkSpace\Model\Table;

use ArrayObject;
use Cake\Event\Event;
use Cake\ORM\Table;
use Cake\ORM\Entity;
use WorkSpace\Model\Entity\WorkSpace;
use Cake\Network\Session;
use Cake\Validation\Validator;
use Cake\ORM\RulesChecker;
use AppServiceUtil\Utility\AppServiceUtil;
use Cake\Network\Exception\BadRequestException;
use Cake\DataSource\ConnectionManager;
use AppServiceUtil\Model\Table\UtilTableTrait;
use Cake\I18n\Time;


/**
 * Workspace Model
 */
class WorkSpaceTable extends Table {

    use UtilTableTrait;

    /**
     * Initialize method
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config) {
        $this->table('m_workspaces');
        $this->addBehavior('Timestamp');

		$this->addBehavior('ShadowTranslate.ShadowTranslate',[
			 'translationTable' => 'm_workspaces_i18n'
        ]);
    }

    public static function defaultConnectionName() {
        return 'default'; //Set whatever required
    }
}
