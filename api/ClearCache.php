<?php

function rrmdir($dir) {
    foreach (glob($dir . '/*') as $file) {
        if (is_dir($file)) {
            rrmdir($file);
        } else {
            unlink($file);
        }
    }
}

rrmdir(dirname(__FILE__) . '/../cache');
?>
