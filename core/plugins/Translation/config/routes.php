<?php
use Cake\Routing\Router;
/*
Router::scope('/Translation', ['plugin' => 'Translation'], function ($routes) {
	//Router::connect('/Security', ['plugin' => 'Security', 'controller' => 'Security']);
	//Router::connect('/Security/:action/*', ['plugin' => 'Security', 'controller' => 'Security']);
});
*/


Router::connect('/translations', ['plugin' => 'Translation','controller' => 'Translations']);
Router::connect('/translations/:action/*', ['plugin' => 'Translation','controller' => 'Translations']);



