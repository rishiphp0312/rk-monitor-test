<?php

namespace ApiService\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;
use Cake\I18n\Time;
use Cake\Core\Plugin;

/**
 * Service Component
 */
class ServiceComponent extends Component {

    private $controller = NULL;
    // The other component your component uses
    public $components = ['Auth', 'User.UserCommon', 'Translation.Translation', 'User.RoleCommon', 'User.ManageUser', 'Module.ModuleCommon', 'Module.PermissionCommon', 'Tag.TagCommon', 'Tag.TagItemCommon', 'WorkSpace.WorkSpaceCommon'];
    public $excludedAuthenticatedApiActions = [];

    /*
     * initialize method
     * @param array $config to populate initialize level config to this component
     */

    public function initialize(array $config) {
        parent::initialize($config);
        $this->session = $this->request->session();
        $this->controller = $this->_registry->getController();
    }

    /*
     * Function to check if authentication is required for a service api method or not
     * @Params actionName/actionCode
     * @return true/false
     */

    public function checkActionNeedAuthentication($action) {
        if (in_array($action, $this->excludedAuthenticatedApiActions)) {
            return FALSE;
        }
        return TRUE;
    }

    /*
     * serviceInterface method
     * Used as an Interface to call a specific plugin component method
     * @param string $plugin Plugin to load and use
     * @param string $component Component to call
     * @param string $method Method to call
     * @return Mixed array/string/int 
     */

    public function serviceInterface($plugin, $component, $method, $params = []) {
        $componentName = $plugin . "." . $component;
        //Check if plugin is not loaded load it
        if (!Plugin::loaded('$plugin')) {
            Plugin::load($plugin, array('bootstrap' => false, 'routes' => true));
        }
        if (!is_object($this->{$component})) {
            $this->controller->loadComponent($componentName);
        }

        if ($component . 'Component' == (new \ReflectionClass($this))->getShortName()) {
            return call_user_func_array([$this, $method], $params);
        } else {
            //pr($this->{$component});exit;
            return call_user_func_array([$this->{$component}, $method], $params);
        }
    }

    /*
     * buildServiceResponse method
     * Used to serve response for web service api call
     * @param string $response arr to build api response data
     * @return Json Response
     */

    public function buildServiceResponse($response = []) {

        // Initialize Result		
        $returnData = [];
        $success = $isSuperAdmin = false;
        $errCode = $errMsg = '';

        // Check response action and take action further
        if (isset($response['status']) && $response['status'] == true) {
            $success = true;
        } else {
            $hasError = isset($response['hasError']) ? $response['hasError'] : FALSE;
            $errCode = isset($response['errCode']) ? $response['errCode'] : '';
            $errMsg = isset($response['errMsg']) ? $response['errMsg'] : '';
        }

        // Set Result
        $returnData['success'] = $success;
        $returnData['err'] = ['code' => '', 'msg' => ''];

        // Check if status is true
        if ($success) {
            $returnData['data'] = isset($response['data']) ? $response['data'] : [];
        }
        // If status is false
        else {
            $returnData['data'] = [];
            $returnData['err']['code'] = $errCode;
            $returnData['err']['msg'] = $errMsg;
        }

        // Finally Convert the response to JSON
        $returnData = json_encode($returnData);

        // Return Result
        if (!$this->request->is('requested')) {
            $this->controller->response->charset('UTF-8');
            $this->controller->response->body($returnData);
            return $this->controller->response;
        } else {
            return $returnData;
        }
    }

    /**
     * send Response header to AJAX request
     * 
     * @param integer $code HTTP request code
     */
    public function sendResponseHeader($code) {
        switch ($code):
            //-- Unauthorized
            case 401:
                header('X-PHP-Response-Code: 401', true, 401);
                break;

            //-- Forbidden
            case 403:
                header('X-PHP-Response-Code: 403', true, 403);
                break;

            //-- Not Found
            case 404:
                header('X-PHP-Response-Code: 404', true, 404);
                break;

            //-- Internal Server Error
            case 500:
                header('X-PHP-Response-Code: 500', true, 500);
                break;

            //-- Invalid Header Code
            default:
                echo 'INVAID HEADER CODE';
                break;
        endswitch;

        exit;
    }

}
