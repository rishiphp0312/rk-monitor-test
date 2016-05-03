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

use Cake\Core\Configure;
use Cake\Network\Exception\NotFoundException;
use Cake\View\Exception\MissingTemplateException;
use Cake\I18n\I18n;

use Cake\ORM\TableRegistry;
use Cake\ORM\Behavior\TranslateBehavior;

/**
 * Static content controller
 *
 * This controller will render views from Template/Pages/
 *
 * @link http://book.cakephp.org/3.0/en/controllers/pages-controller.html
 */
class TestController extends AppController
{

    
    public function initialize() {
        parent::initialize();
                I18n::locale('en');

    }

        public function testdata(){
        

        // Then you can change the language in your action:
        $this->loadModel('MRolesEn');
        $this->loadModel('Rolesi18nTable');
        
        $article = $this->MRolesEn->find('translations')->first();
echo $article->translation('es')->role_name; // 'Un Artículo'

pr($article);die;
        $this->MRolesEn->locale('fr'); // specific locale
        $article = $this->MRolesEn->get(12);
        pr($article);
        echo $article->role_name; 
        die;
/*
*/
    
       $article = $this->MRolesEn->find('translations',['locales' => ['en', 'es']])->hydrate(false)->first();
         pr($article);die;
         $data = $this->MRolesEn->find('all', ['fields'=>['role_name']],['conditions'=>['id'=>12]])->hydrate(false)->first();
         pr($data);die;
         // Outputs 'en'
echo $article->_translations['en']->locale;

// Outputs 'title'
echo $article->_translations['en']->field;

// Outputs 'My awesome post!'
echo $article->_translations['en']->body;
        //$results = $this->MRolesEn->find()->hydrate(false)->all();
        pr($data);die;
    }
    
    public function savetrans(){
        $translations = [
            'fr' => ['title' => "abc test"],
            'en' => ['title' => 'jai debv']
         ];
        $this->loadModel('MRolesEn');

        foreach ($translations as $lang => $data) {
            $article->translation($lang)->set($data, ['guard' => false]);
        }

$this->Articles->save($article);
    }
}
