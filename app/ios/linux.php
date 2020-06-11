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
        $file = store("/etc/apache2/sites-available/000-default.conf")->makeHasFile;
        $is_done = $file->setContent(`<VirtualHost *>
            ServerAdmin admin@localhost
            DocumentRoot /var/www/html
            <Directory /var/www/html>
                Options Indexes
                    Require all granted
            </Directory>
            ErrorLog logs/error.log
            CustomLog logs/access.log combined
        </VirtualHost>`);
        line($is_done?'Creado el default':'Error al crear default');
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