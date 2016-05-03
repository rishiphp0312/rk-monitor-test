<?php

$errorLogFilepath =  str_replace('webroot', '', getcwd()) . 'logs' . DS . 'error.log';
echo '<pre>';
file_put_contents($errorLogFilepath, 'Emptied at -> ' . date('Y-m-d H:i:s', time()));
echo file_get_contents($errorLogFilepath);
exit;