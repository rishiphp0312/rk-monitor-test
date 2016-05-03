<?php

$serverScheme = "http://";
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
    $serverScheme = "https://";
}
// Defining CONSTANTS
$website_base_url = $serverScheme . $_SERVER['HTTP_HOST'];
$website_base_url .= preg_replace('@/+$@', '', dirname($_SERVER['SCRIPT_NAME'])) . "/";
$website_base_url = str_replace('webroot/', '', $website_base_url);
$extra_folder = str_replace('webroot', '', getcwd()) . 'extra';
$website_base_url = str_replace("\/", "/", $website_base_url);
return [
    define('_WEBSITE_URL', $website_base_url),
    define('WEBSITE_URL', $website_base_url),
    // Delemeters
    define('DELEM1', '{~}'),
    define('DELEM5', '{-}'),
    define('DELEM2', '[~]'),
    define('DELEM3', '-'), // used in  salt explode for activation key
    define('DELEM4', '_'),
    define('DELEM6', ','),
    define('FORGOTPASSWORD_SUBJECT', 'DFA-Monitoring - Reset your password'),
    define('SALT_PREFIX1', 'abcd#####'), // used in  activation key 
    define('SALT_PREFIX2', 'abcd###*99*'), // used in   activation key 
    define('APP_NAME', 'DFA-Monitoring'), // used as application name 
    define('ADMIN_EMAIL', 'vktiwari@avaloninfosys.com'), // used as admin email 
    define('LANGUAGES_DEFAULT_VALUE', 1), //  value of default language  
    define('UPLOADSDIR', 'uploads'),
    define('TRANSLATIONS_PATH_WEBROOT', UPLOADSDIR . '/' . 'TRANSLATIONS'),
    define('XLS_PATH_WEBROOT', UPLOADSDIR . '/' . 'xls'),
    define('XLS_PATH', WWW_ROOT . XLS_PATH_WEBROOT),
    define('PO_BCKP_PATH', WWW_ROOT . DS .UPLOADSDIR.DS.'po_bck'),//po_bck dir
    define('SUCCESS','SUCCESS'),
    define('FAILED','FAILED'),
    define('BLOCKED','BLOCKED'),
    define('SYSTEM_ROLE_YES',1),
    define('SYSTEM_ROLE_NO',0),
    define('GLOBAL_ADMIN_COUNTRY_ID','-1'), //global admin country id  
    define('GLOBAL_ADMIN_WORKSPACE_ID','1'), //global admin workspace  id  
    define('GLOBAL_ADMIN_GID_ID', 'GA'), //global admin gid
    define('COUNTRY_ADMIN_ROLE_ID',2), //System defined role id of country admin 
    define('IS_DELETED_YES',1), //soft deleted 
    define('IS_DELETED_NO',0), //not  deleted 
    //define('SYSTEM_ROLE',1),

	// Get Application default language
	define('APP_DEF_LANGUAGE', 'en'),
     

    
];
?>

