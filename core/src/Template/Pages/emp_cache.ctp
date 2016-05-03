<?php

function filesToDelete($fileToDelete) {
    foreach ($fileToDelete as $file) {
        if (is_file($file))
            unlink($file);
    }
}

$modelspath =  str_replace('webroot', '', getcwd()) . 'tmp' . DS . 'cache' . DS . 'models';
$persistentpath =  str_replace('webroot', '', getcwd()) . 'tmp' . DS . 'cache' . DS . 'persistent';

// DELETE Models Cache
filesToDelete(glob($modelspath . '/*'));

// DELETE Persistent Cache
filesToDelete(glob($persistentpath . '/*'));

exit;

