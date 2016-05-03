<?php

namespace Module\Controller\Component;

use Cake\Controller\Component;
use Cake\Event\Event;
use Cake\Core\Configure;
use Cake\Network\Http\Client;
use Cake\ORM\TableRegistry;
use Cake\I18n\Time;
use AppServiceUtil\Utility\AppServiceUtil;
use Cake\View\View;

/**
 * User common Component used to perform user related activities
 */
class PermissionCommonComponent extends Component {

    public $components = ['AppServiceUtil.UtilCommon'];

    public function initialize(array $config) {
        parent::initialize($config);
    }

    /**
     * Method to get role and module association
     * @param 
     * @return association array
     */
    public function getRoleModuleRel() {
        // Load module from security plugin
        $rolesPermsnTable = TableRegistry::get('Security.RolePermissions');
        // Temporary binding 
        $rolesPermsnTable->belongsTo(
                'Modules',[
                    'className' => 'Security.Modules',
                    'foreignKey' => 'module_id'
                ],    
                'PermissionDetails',[
                    'className' => 'Security.PermissionDetails',
                    'foreignKey' => 'permission_detail_id'
                ]
        );
        $postData = $this->request->data;
        // Check required parameters
        if (empty($postData['role_id'])) {
            return AppServiceUtil::errResponse('MISSING_PARAMETERS');
        } else {
            $role_id = $postData['role_id'];
        }
        // Get data related to this primary key
        $query = $rolesPermsnTable->find('all')
                ->where(['RolePermissions.id' => $role_id])
                ->contain(['Modules','PermissionDetails']);
        $data = $query->first();
        if (!$data) { // Return error
            return AppServiceUtil::errResponse('INVALID ID');
        } else {
            $data = $data->toArray();
            echo '<pre>';
            print_r($data);
            die;
        }
        die;
        //if()
        var_dump($roleInfo);
        die;
        if (in_array($this->globalAdminRoleId, $roleData['roles'])) {
            return $this->UserTblObj->getCountryAdmins();
        } else { // Country specific list
            // If fields are specified
            if (!empty($postData['fields'])) {
                $fields = $postData['fields'];
            }
            // Debugging request
            if (!empty($postData['extra'])) {
                $extra = $postData['extra'];
            }
            if (!empty($roleData['country_id'])) {
                $conditions = ['country_id' => $roleData['country_id']];
            }
            // Get list of country specific users
            return $this->UserTblObj->getRecords($fields, $conditions, $type, $extra);
        }
        return [];
    }

}
