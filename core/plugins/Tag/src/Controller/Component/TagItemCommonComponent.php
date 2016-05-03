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
class TagItemCommonComponent extends Component {

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
		$this->tagItemTblObj = TableRegistry::get('Tag.TagItem');
        $this->controller = $this->_registry->getController();
    }

	public function getTagItemList (){
		$data = [];
        $moduleId = 1;
       
        if (!empty($moduleId)) {
            $data['Module'] = $this->tagTblObj->getFirst([], ['id' => $moduleId]);
        }else{
             return AppServiceUtil::errResponse('ROLEID_BLANK_MSG');
        }
		return($data);		
	}

	public function createTagItems ()
	{
		$loggedUserId = 1;//$this->Auth->User('id');
		$currSelLang = $this->controller->getAppCurrentLanguage();
		
		/*// Create :: Save record in multiple languages
		$tagId = 30;
		$tagItemCaption = "Donors 3";
		$tagItemDesc = "$tagItemCaption Description";
		$data=['area_id'=>-1, 'tag_id'=>$tagId, 'tag_item_gid'=>rand(),'tag_item_caption'=>$tagItemCaption, 'tag_item_description'=>$tagItemDesc, 'icon'=>rand(), 'created_user_id'=> $loggedUserId, 'modified_user_id'=> $loggedUserId];
        $tagLangData = [   
			'en' => ['tag_item_caption' => "$tagItemCaption en", 'tag_item_description' => "$tagItemDesc en" ],
			'fr' => ['tag_item_caption' => "$tagItemCaption fr", 'tag_item_description' => "$tagItemDesc fr"]
		];
              
		$tagData = $this->tagItemTblObj->newEntity();            
		$tagData = $this->tagItemTblObj->patchEntity($tagData, $data);
		$this->tagItemTblObj->save($tagData);	
		$newTagId = $tagData->id;
		echo"<br>Step 2--->";

		foreach ($tagLangData as $lang => $data) { 
			I18n::locale($lang);	
			$TagItem = TableRegistry::get('Tag.TagItem')->newEntity($data);
			$TagItem->id = $newTagId;
			$this->tagItemTblObj->save($TagItem);
		}*/
		
		// Find method
		// Get single record
		/*I18n::locale($currSelLang);	
		// Get single record
		$TagItems = $this->tagItemTblObj->get(2);

		// Get all record
		$TagItems = $this->tagTblObj->find()->all()->toArray();	*/

		// Get record from association
		$TagItems = $this->tagItemTblObj->find()->contain(['Tag'])->first();			
		pr($TagItems);

		// Delete Method
		/*$Tag = $this->tagTblObj->get(27);
		$this->tagTblObj->delete($Tag);*/
		
		echo "<br>createTagItems--->";exit;
	}

}
