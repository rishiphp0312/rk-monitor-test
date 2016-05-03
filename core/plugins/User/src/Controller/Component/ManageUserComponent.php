<?php

namespace User\Controller\Component;

use Cake\Controller\Component;
use Cake\Event\Event;
use Cake\Core\Configure;
use Cake\Network\Http\Client;
use Cake\ORM\TableRegistry;
use Cake\I18n\Time;
use AppServiceUtil\Utility\AppServiceUtil;
use User\Controller\Component\UserCommonComponent;
use Cake\Auth\DefaultPasswordHasher;
use Cake\View\View;

/**
 * User common Component used to perform user related activities
 */
class ManageUserComponent extends Component {

    public $components = ['AppServiceUtil.UtilCommon','Auth'];
    
    public function initialize(array $config) {
        parent::initialize($config);
    }
    
    /**
     * method to assing role to a user 
     * @param type $roleName
     */
    public function assignRole() {
        $userRoleTbl=TableRegistry::get('User.UserRoles');
        $postData = $this->request->data;
        // Convert fields name
        $postData = AppServiceUtil::backendFrontendFieldsMap('UserRoles', $postData, false, array());
        // New data created by loggedIn use
        $user=$this->Auth->user();
        
        $userId=$user['id'];
        //Other info 
        $postData['created_user_id'] = $userId;
        // Save user info [Auto validation In model]
        $result =$userRoleTbl->addUserRole($postData);
        return $result;
    }
}