<?php

spl_autoload_register(function($class){
    if(file_exists(__DIR__."/class/$class.php"))
        include __DIR__."/class/$class.php";
});
include __DIR__.'/miscellaneous.php';
include __DIR__.'/GLOBALS.php';