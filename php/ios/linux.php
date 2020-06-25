<?php
return [
    'path'=>[
        'hosts'=>'/etc/hosts',
        'pages'=> param('public-path'),
        'vhosts'=>'/etc/apache2/sites-available/',
    ],
    'tld'=>'.io',
    'before'=>function(){
        param("remote")?config("tld", null):null;
        line("# Iniciado #");
        line("Deshabilitando sitios...");
        Console::run("sudo systemctl stop apache2");
        Console::run("sudo rm -rf /etc/apache2/sites-available/*.conf");
        Console::run("sudo rm -rf /etc/apache2/sites-enabled/*.conf");
        store("/etc/apache2/sites-available/000-default.conf")
            ->makeHasFile
            ->setContent("<VirtualHost *:80>\nServerAdmin admin@localhost\nDocumentRoot /var/www/html\n<Directory /var/www/html>\nOptions Indexes\nRequire all granted\n</Directory>\n</VirtualHost>");
        line("Refrescando rutas...");
    },
    'add'=>function($item){
        $file=store($this->path['vhosts'].$item->CONF_FILE)->unlink;
        $is_done=$file->makeHasFile->setContent(template('VirtualHost',$item));
        line(($is_done?'Agregado: ':'Error: ').$item->DOMAIN_NAME);
        return $is_done?$item:null;
    },
    'after'=>function(){
        line("Reiniciando apache2...");
        Console::run("sudo systemctl start apache2");
        Console::run("sudo a2ensite *.conf");
        Console::run("sudo systemctl reload apache2");
        line("# Terminado #");
    },
];