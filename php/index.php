<?php
require __DIR__."/autoload.php";

/* Refrescar la lista de Dominios */
if(param('fresh')){
    config('before')();
    line(store(config()->path->pages)->find('*.*')
    ->filter(function($info){
        return in_array($info->basename, DOMAIN_LIST??[$info->basename]);
    })
    ->map(function($info){
        $alias = $info->filename;
        $tld = clean('.',input('tld',$info->extension));
        return collect([
            'DOCUMENT_ROOT'=>(store($info->real->path)->find('/public*')[0]??['path'=>$info->real->path])['path'],
            'ALIAS'=>$alias,
            'TLD'=>$tld,
            'PORT'=>input('port',80),
            'DOMAIN_NAME'=>"$alias.$tld",
            'CONF_FILE'=>"$info->filename.conf",
        ]);
    })
    ->map(function($item){ return config()->add($item); })->pop()
    ->if(function(){
        $sites=$this;
        return store(config('path')->hosts)->makeHasFile->setContent(function($prev)use(&$sites){
            $inside=false;
            $lines = explode("\n", $prev);
            $lines = array_filter($lines, function($line) use(&$inside){
                if(preg_match("/^\#+\s+Inicio \- PHPSiteManager\s+\#+/", $line)) $inside=true;
                else if(preg_match("/^\#+\s+Fin \- PHPSiteManager\s+\#+/", $line)) return $inside=false;
                return !$inside&&!empty($line);
            });
            array_push($lines,'###### Inicio - PHPSiteManager ######');
            $sites->each(function($item) use(&$lines){
                array_push($lines,
                    "127.0.0.1  $item->DOMAIN_NAME"
                );
            });
            array_push($lines,'###### Fin - PHPSiteManager ######');
            return implode("\n",$lines);
        });
    })?'Dominios Registrados':'No pudimos registrar los nombres de dominios');
    config('after')();
}


if(param("dev")||param("npm")||param("composer")){
    $resources = store(__DIR__.'/../resources/');
    store(config('path')->pages)
        ->find("*.*")
        ->each(function($site) use(&$resources){
            $installed=[];
            if($resources->find('composer')&&$site->find('composer.json')&&(!count($installed)||param("composer"))){
                $file = $site->open('composer.json');
                $vendor = $site->open('vendor');
                $array = json_decode($file->getContent, true);
                $resources
                    ->open('composer/')->folders->map('store')
                    ->each(function($plugin)use(&$array,&$vendor){
                        $array['require']["arcaela/$plugin->filename"]='dev-master';
                        if($vendor->open("arcaela/$plugin->filename")->linkTo($plugin->path, true))
                            line("Instalado: arcaela/$plugin->filename");
                        else line("No instalado: arcaela/$plugin->filename");
                    });
                $file->setContent(str_replace(['\/','": "'],['/','":"'],json_encode($array, JSON_PRETTY_PRINT)));
            }
            if($resources->find('npm')&&$site->find('package.json')&&(!count($installed)||param("npm"))){
                $file = $site->open('package.json');
                $node_modules = $site->open('node_modules');
                $array = json_decode($file->getContent, true);
                $resources
                    ->open('npm/')->folders->map('store')
                    ->each(function($plugin)use(&$array,&$node_modules){
                        $array['dependencies'][$plugin->filename]='latest';
                        if($node_modules->open($plugin->filename)->linkTo($plugin->path, true))
                            line("Instalado: ".$plugin->filename);
                        else line("No instalado: ".$plugin->filename);
                    });
                $file->setContent(str_replace(['\/','": "'],['/','":"'],json_encode($array, JSON_PRETTY_PRINT)));
            }
        });
}

if(param('info')){
    line("\n###### PHP Virtual Manager for ".iOS()." ######\n");
    line("PHP Version: ".phpversion());
    line("Domains path: ".store(config("path")->pages)->real->and('/') );
    line("TLD: ".config()->tld );
    line("Arguments: ".$_PARAMS);
    line("Domains: ".(
        store(config()->path->pages)
        ->find('*.*')
        ->filter(function($info){
            return in_array($info->basename, DOMAIN_LIST??[$info->basename]);
        })->map(function($e){
            return $e->basename;
        })
    ));
}
