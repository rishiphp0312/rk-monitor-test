<?php

namespace Tag\Controller\Component;

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
class TagCommonComponent extends Component {

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
        $this->tagTblObj = TableRegistry::get('Tag.Tag');
        $this->controller = $this->_registry->getController();
    }

	public function getTagList (){
		$data = [];
        $moduleId = 1;
       
        if (!empty($moduleId)) {
            $data['Module'] = $this->tagTblObj->getFirst([], ['id' => $moduleId]);
        }else{
             return AppServiceUtil::errResponse('ROLEID_BLANK_MSG');
        }
		return($data);		
	}

	public function createTags ()
	{
		$loggedUserId = 1;//$this->Auth->User('id');
		$currSelLang = $this->controller->getAppCurrentLanguage();
		
		// Create :: Save record in multiple languages
		$tagCaption = 'Resources';
		$tagDesc = "$tagCaption Description";
		$data=['area_id'=>-1, 'tag_gid'=>rand(),'is_system_tag'=>0,'is_mandatory'=>1,'modified_user_id'=>1,'created_user_id'=>1, 'tag_caption'=>$tagCaption, 'tag_description'=>$tagDesc];       
              
		$tagData = $this->tagTblObj->newEntity();            
		$tagData = $this->tagTblObj->patchEntity($tagData, $data);
		$this->tagTblObj->save($tagData);	
		$newTagId = $tagData->id;
		echo"<br>Step 2--->";

		$tagLangData = [   
			'en' => ['tag_caption' => "$tagCaption en", 'tag_description' => "$tagDesc en" ],
			'fr' => ['tag_caption' => "$tagCaption fr", 'tag_description' => "$tagDesc fr"]
		];

		foreach ($tagLangData as $lang => $data) { 
			I18n::locale($lang);	
			//$Tag = TableRegistry::get('Tag.Tag')->newEntity(['id' => $tagData->id,'tag_caption' => 'My First tag '.$lang, 'tag_description' => 'content tag description '.$lang]);
			$Tag = TableRegistry::get('Tag.Tag')->newEntity($data);
			$Tag->id = $newTagId;
			$this->tagTblObj->save($Tag);
		}
		die;
		// Find method
		// Get single record
		I18n::locale($currSelLang);	
		/*// Get single record
		$Tag = $this->tagTblObj->get(26);
		
		// Get all record
		$Tag = $this->tagTblObj->find()->all()->toArray();*/

		// Get record from association
		//$Tag = $this->tagTblObj->find()->contain(['TagItem']);
		$query = $this->tagTblObj->find('all', ['conditions' => ['id' => 30],'contain' => ['TagItem']]);
		$Tag = $query->first();
		pr($Tag);
		

		// Delete Method
		//$Tag = $this->tagTblObj->get(32);
		//$this->tagTblObj->delete($Tag);
		
		echo "<br>create_tags--->";exit;
	}

}
