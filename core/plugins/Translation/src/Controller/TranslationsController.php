<?php

namespace Translations\Controller;

use Translations\Controller;
use Cake\Core\Configure;
use Cake\Network\Exception\NotFoundException;
use Cake\View\Exception\MissingTemplateException;
use Cake\Event\Event;
use AppServiceUtil\Utility\AppServiceUtil;

/*
 * Translations Controller
 */

class TranslationsController extends AppController {

    public $name = 'Translations';

    public function initialize() {
        parent::initialize();

        $helpers = ['Html'];
        $this->session = $this->request->session();
    }

    //services/serviceQuery
    public function beforeFilter(Event $event) {
        parent::beforeFilter($event);
        $this->Auth->allow(['logout', 'login']);
    }

    /**
     * index method
     * Users loginRedirect action 
     */
    public function index() {
        // $this->autoRender = FALSE;       
        $this->layout = 'service';
    }

}
