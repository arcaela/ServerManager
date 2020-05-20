<?php
    require __DIR__."/../autoload.php";
    line("\n###### PHP Virtual Manager for ".iOS()." ######\n");
    line("PHP Version: ".phpversion());
    line("Domains path: ".path(config("path")->pages)->real->path );
    line("TLD: ".config()->tld );
    line("Arguments: ".$_PARAMS);
    line("Domains: ".(
        path(config()->path->pages)
        ->glob('*.*')
        ->filter(function($info){
            return in_array($info->basename, DOMAIN_LIST??[$info->basename]);
        })->map(function($e){
            return $e->basename;
        })
    ));