<?php

namespace WorkSpace\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;
use Cake\I18n\Locale;
use Cake\I18n\Time;
use AppServiceUtil\Utility\AppServiceUtil;
use Cake\View\View;
use Cake\I18n\I18n;

/**
 * Role common Component used to perform user related activities
 */
class WorkSpaceCommonComponent extends Component {

    public $modTblObj = NULL;
    public $controller = '';
    public $components = ['AppServiceUtil.UtilCommon', 'Auth'];

    /*
     * Initialize function to setup some initial valrs and call method to do intial function
     * @access public
     * @params $config array to populate initialize time config vars to user component
     */

    public function initialize(array $config) {
        parent::initialize($config);
        $this->session = $this->request->session();
        $this->WorkSpaceTblObj = TableRegistry::get('WorkSpace.WorkSpace');
        $this->controller = $this->_registry->getController();
    }

	public function getWorkSpaceList ($params=''){

		$currSelLang = $this->controller->getAppCurrentLanguage ();		
		// Get single record
		I18n::locale($currSelLang);	
		$data = [];

		// Get record from association		
		$query = $this->WorkSpaceTblObj->find('all', ['conditions' => ['id' => 2]]);
		$data = $WorkSpace = $query->first();
		//pr($WorkSpace);		
		
		//echo "<br>getWorkSpaceList--->";exit;
		return($data);		
	}

	public function createWorkSpace ()
	{
		
		// Find method
		// Get single record
		I18n::locale($currSelLang);	

		// Get record from association		
		$query = $this->WorkSpaceTblObj->find('all', ['conditions' => ['id' => 2]]);
		$WorkSpace = $query->first();
		pr($WorkSpace);
		
		
		echo "<br>createWorkSpace--->";exit;
	}

}
