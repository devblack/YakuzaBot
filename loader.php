<?php
spl_autoload_register(function ($class) {
    //paths of files with the extension
    $dirs = array('config/', 'lib/');
    //extension name, you can change it, but take care.
    $extension = ".yakuza.php";

    foreach ($dirs as $dir) {
        $fpath = $dir.$class.$extension;
        if (file_exists($fpath)) {require $fpath;}
    }
});