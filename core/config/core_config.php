<?php
$domainRootUrl = function() {
    return ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != "off") ? true : false)
            ? 'https' . "://" . $_SERVER['HTTP_HOST'] . "/"
            : 'http' . "://" . $_SERVER['HTTP_HOST'] . "/";
};
return [
    'AUTHENTICATION' => [
        'HTTP_HOST' => $domainRootUrl(),
        'IS_EXTERNAL_AUTHENTICATION' => false,
        'SSO_URL' => $domainRootUrl() . 'dfa_monitoring/portal/global/fetchsession/',
        'SSO_LOGOUT_URL' => $domainRootUrl() . 'dfa_monitoring/portal/global/logout/'
    ],
];