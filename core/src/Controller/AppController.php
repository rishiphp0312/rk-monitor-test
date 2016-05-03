<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link      http://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use Cake\Controller\Controller;
use Cake\Event\Event;
use Cake\Network\Http\Client;
use Cake\I18n\Time;
use Cake\Core\Configure;
use Cake\Routing\Router;
use Cake\ORM\TableRegistry;

Time::$defaultLocale = 'es-ES';
Time::setToStringFormat('YYYY-MM-dd HH:mm:ss');

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @link http://book.cakephp.org/3.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller
{

    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * e.g. `$this->loadComponent('Security');`
     *
     * @return void
     */
    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('RequestHandler');
        $this->loadComponent('Flash');
    }

    public function beforeFilter(Event $event) {
		//$session = $this->request->session();
		//pr($session->read('Auth'));die;
        $this->onSSOFilter();
    }	
	
    /**
     * Before render callback.
     *
     * @param \Cake\Event\Event $event The beforeRender event.
     * @return void
     */
    public function beforeRender(Event $event)
    {
        if (!array_key_exists('_serialize', $this->viewVars) &&
            in_array($this->response->type(), ['application/json', 'application/xml'])
        ) {
            $this->set('_serialize', true);
        }
    }

	/**
	* Function to get app current language
	*
	* @return siteLanguage string
	*/
    public function getAppCurrentLanguage (){
		
		// Get session object
		$session = $this->request->session();

		// Read app currently selected language 
		$siteLanguage = $session->read('Config.language');

		//If app selected language not set
		if(empty($siteLanguage)) {
			// Set application current selected language
			$siteLanguage = APP_DEF_LANGUAGE;
			$session->write('Config.language', $siteLanguage);
		}
		return $siteLanguage;
	}
	
    /*
     * Function to process remote based logout
     * @Params : none
     * Remark : Feature under discussion, 
     * Just for now, user will get redirect on portal if SSO session does not exists
    */
    public function onSSOFilter() {
        if (Configure::read('AUTHENTICATION.IS_EXTERNAL_AUTHENTICATION')) {
			$DFAMON_SSN_ID = isset($_COOKIE["DFAMON_SSN_ID"]) ? $_COOKIE["DFAMON_SSN_ID"] : "";
            if (!isset($DFAMON_SSN_ID) || empty($DFAMON_SSN_ID)) {
                $ssoLogoutURL = Configure::read('AUTHENTICATION.SSO_LOGOUT_URL');
                @session_unset();
                @session_destroy();
                $this->redirect($ssoLogoutURL);
            }
        }
    }
	
}
