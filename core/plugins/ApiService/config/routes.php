<?php
use Cake\Routing\Router;

Router::scope('/api', ['plugin' => 'ApiService'], function ($routes) {
	Router::connect('/api', ['plugin' => 'ApiService', 'controller' => 'Services']);
	Router::connect('/api/:action/*', ['plugin' => 'ApiService', 'controller' => 'Services']);
});



