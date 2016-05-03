<?php
use Cake\Routing\Router;

Router::connect('/users', ['plugin' => 'User','controller' => 'Users','action' => 'login']);
Router::connect('/test', ['plugin' => 'User','controller' => 'Users','action' => 'test']);
Router::connect('/users/:action/*', ['plugin' => 'User','controller' => 'Users']);

/*Router::scope('/Users', ['plugin' => 'User'], function ($routes) {
	Router::connect('/Users', ['plugin' => 'User', 'controller' => 'Users']);
	Router::connect('/Users/:action/*', ['plugin' => 'User', 'controller' => 'Users']);
});*/

