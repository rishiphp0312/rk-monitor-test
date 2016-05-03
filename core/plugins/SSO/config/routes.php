<?php

use Cake\Routing\Router;

Router::scope('/SSO', ['plugin' => 'SSO'], function ($routes) {
    Router::connect('/SSO', ['plugin' => 'SSO',
        'controller' => 'Authenticates',
        'action' => 'login']
    );
    Router::connect('/SSO/:action/*', ['plugin' => 'SSO',
        'controller' => 'Authenticates']
    );
});
