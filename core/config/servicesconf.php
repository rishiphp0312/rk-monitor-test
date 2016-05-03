<?php
return [
    /**
     * Custom configuation values 
     */
     /* Web Services configuration params to exclude actions from authentication and authorisation
     *
     */
    'ExcludeAuthenticateActions' => ['login'],
    'ExcludeAuthoriseActions' => ['login'],
	'excludeActionsSettings' => [100=>array('plugin_name'=>'User', 'component_name'=>'UserCommon', 'action_name'=>'processFormLogin'),
	101=>array('plugin_name'=>'User', 'component_name'=>'UserCommon', 'action_name'=>'logoutUser'),
              102=>array('plugin_name'=>'User', 'component_name'=>'UserCommon', 'action_name'=>'forgotPassword'),
              103=>array('plugin_name'=>'User', 'component_name'=>'UserCommon', 'action_name'=>'accountActivation'),
             104=>array('plugin_name'=>'User', 'component_name'=>'UserCommon', 'action_name'=>'getLoggedInDetails'),
             2008=>array('plugin_name'=>'Translation', 'component_name'=>'Translation', 'action_name'=>'getLanguageList'),
             2009=>array('plugin_name'=>'Translation', 'component_name'=>'Translation', 'action_name'=>'readPublishedLanguage'),
            ],
];
