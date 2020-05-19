<?php
    require realpath(__DIR__."/app/autoload.php");

    // Ejecutamos una funcion antes de comenzar
    config()->before();
    // Obtenemos la informacion de la ruta
    path(config()->path->pages)
        ->glob('*.*')
        ->filter(function($info){
            return in_array($info->basename, DOMAIN_LIST??[$info->basename]);
        })
        // Creamos los elementos
        ->map(function($info){
            return collect([
                'SERVER_ROOT'=>$info->path,
                'DOCUMENT_ROOT'=>path(glob($info->path.'/public*')[0]??$info->path)->path,
                'DOMAIN_NAME'=>preg_replace('/\.+/', '.', $info->filename.'.'.(config('tld')??$info->extension)),
                'CONF_FILE'=>$info->filename.(IS_SSL?'-ssl':'').'.conf',
            ]);
        })
        // Agregamos el Sitio Web
        ->map(function($site){ return config()->add($site); })
        ->pop()
        // Registramos el dominio en el hosts
        ->if(function(){
            $hosts = config('path')->hosts;
            $tmp_domains=$this->map(function($item){
                return $item->DOMAIN_NAME;
            })->toArray();
            return file_put_contents($hosts,
                join("\n",array_filter([
                    ...array_map(function($line)use($tmp_domains){
                        $have = count(array_filter($tmp_domains,function($d)use($line){
                            return preg_match("/".str_replace('.','\.',$d)."/",$line);
                        }));
                        if(preg_match("/\#+/",$line))
                            return $have?$line:null;
                        else if($have)
                            return null;
                        return $line;
                    },explode("\n",(is_file($hosts)?file_get_contents($hosts):""))),
                    "############ VirtualHost PHP ############",
                    ...array_map(function($domain){
                        return "127.0.0.1   ".$domain;
                    },$tmp_domains),
                ]))
            );
        })?'Dominios Registrados':'No pudimos registrar los nombres de dominios';
        // Ejecutamos una funcion al culminar
        config()->after();
    if(param('dev')||param('dependencies')) include __DIR__.'/repo.php';