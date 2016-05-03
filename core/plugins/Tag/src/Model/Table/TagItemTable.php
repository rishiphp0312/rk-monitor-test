<?php

namespace Tag\Model\Table;

use ArrayObject;
use Cake\Event\Event;
use Cake\ORM\Table;
use Cake\ORM\Entity;
use User\Model\Entity\Role;
use Cake\Network\Session;
use Cake\Validation\Validator;
use Cake\ORM\RulesChecker;
use AppServiceUtil\Utility\AppServiceUtil;
use Cake\Network\Exception\BadRequestException;
use Cake\DataSource\ConnectionManager;
use AppServiceUtil\Model\Table\UtilTableTrait;
use Cake\I18n\Time;

/**
 * Module Model
 */
class TagItemTable extends Table {

    use UtilTableTrait;
	/**
     * Initialize method
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config) {
        $this->table('m_tag_items');
        $this->addBehavior('Timestamp');
		
		$this->addBehavior('ShadowTranslate.ShadowTranslate',[
			'translationTable' => 'm_tag_items_i18n'
        ]);

		$this->belongsTo('Tag', [
            'className' => 'Tag.Tag',
            'foreignKey' => 'tag_id'
        ]);
    }

    /**
     * 
     * @return string
     */
    public static function defaultConnectionName() {
        return 'default'; //Set whatever required
    }

   
}
