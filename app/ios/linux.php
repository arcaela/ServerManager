<?php
return [
    'path'=>[
        'hosts'=>'/etc/hosts',
        'pages'=> __DIR__.'/../../www/',
        'vhosts'=>'/etc/apache2/sites-available/',
        'certs'=>'/etc/ssl/',
    ],
    'tld'=>'.io',
    'before'=>function(){
        param("remote")?config("tld", null):null;
        line("Procesando...\n");
        echo Console::run("sudo systemctl stop apache2");
        echo Console::run("sudo rm -rf /etc/apache2/sites-enabled/*");
        echo Console::run("sudo rm -rf /etc/apache2/sites-available/*.conf");
    },
    'add'=>function($item){
        // Registrar el Dominio
        $fn = fopen($this->path->vhosts.$item->CONF_FILE,'a');
        $is_done = fputs($fn, template((IS_SSL?'ssl':(param('ssl-only')?'ssl-only':'VirtualHost')),$item));
        fclose($fn);
        line(($is_done?"Creado: ":"Error: ").$item->DOMAIN_NAME);
        return $is_done?$item:null;
    },
    'after'=>function(){
        line(Console::run("sudo a2ensite *.conf"));
        line(Console::run("sudo systemctl start apache2"));
        line("Hemos terminado");
    },
];