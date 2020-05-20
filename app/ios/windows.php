<?php
return [
    'path'=>[
        'hosts'=>'C:/Windows/System32/drivers/etc/hosts',
        'pages'=>'H:/www/',
        'vhosts'=>'H:/xampp/apache/conf/extra/httpd-vhosts.conf',
        'certs'=>'H:/xampp/apache/conf/ssl/',
        'template'=>[
            "default"=>__DIR__.'/../templates/VirtualHost',
            "ssl"=>__DIR__.'/../templates/ssl',
        ],
    ],
    'tld'=>'.io',
    'before'=>function(){
        echo "Procesando...\n\n";
        if(file_exists($this->path->vhosts)) unlink($this->path->vhosts);
    },
    'add'=>function($item){
        $fn = fopen($this->path->vhosts,'a');
        $t = fputs($fn,str_replace(
            array_map(function($k){ return "<$k>";},$item->keys()),
            $item->values(),
            file_get_contents($this->path->template->default)
        ));
        fclose($fn);
        echo ($t?'Creado: ':'Error: ').$item->DOMAIN_NAME."\n";
        return $t?$item:null;
    },
    'after'=>function(){
        echo "\nHemos terminado\n";
    },
];