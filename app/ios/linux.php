<?php
return [
    'path'=>[
        'hosts'=>'/etc/hosts',
        'pages'=> __DIR__.'/../../www/',
        'vhosts'=>'/etc/apache2/sites-available/',
    ],
    'tld'=>'.io',
    'before'=>function(){
        param("remote")?config("tld", null):null;
        line("Procesando...");
        echo Console::run("sudo a2dissite *.conf");
        echo Console::run("sudo rm -rf /etc/apache2/sites-available/*.conf");
        line(Console::run("sudo systemctl stop apache2"));

    },
    'add'=>function($item){
        $file=store($this->path['vhosts'].$item->CONF_FILE)->unlink;
        $is_done=$file->makeHasFile
        ->setContent(template('VirtualHost',$item));
        return $is_done?$item:null;
    },
    'after'=>function(){
        line(Console::run("sudo a2ensite *.conf"));
        echo Console::run("sudo systemctl start apache2");
        echo Console::run("sudo systemctl reload apache2");
        line("Hemos terminado");
    },
];