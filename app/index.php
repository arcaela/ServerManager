<?php
require __DIR__."/autoload.php";

/* Refrescar la lista de Dominios */
if(param('fresh')){
    line(
        "Hola"
    );
    exit();
    config('before')();
    line(store(config()->path->pages)->find('*.*')
    ->filter(function($info){
        return in_array($info->basename, DOMAIN_LIST??[$info->basename]);
    })
    ->map(function($info){
        return collect([
            'SERVER_ROOT'=>$info->real->path,
            'DOCUMENT_ROOT'=>(store($info->real->path)->find('/public*')[0]??['path'=>$info->real->path])['path'],
            'DOMAIN_NAME'=>preg_replace('/\.+/', '.', $info->filename.'.'.(param('tld')??config('tld')??$info->extension)),
            'CONF_FILE'=>$info->filename.(IS_SSL?'-ssl':'').'.conf',
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

    exit();
    $dist = store(__DIR__.'/../resources/');
    store(config('path')->pages)
        ->find("*.*")
        ->each(function($site) use(&$dist){
            $all=(!param("npm")&&!param("composer"));
            if($dist->find('composer')&&$site->find('composer.json')&&($all||param("composer"))){
                $all=!param('composer');
                $dist->go('composer');
                $file = store($site->and('composer.json'));
                $vendors = store($site->and('vendor'));
                if($file->exist){
                    $content = $file->getContent;
                    if(!preg_match("/arcaela/",$content)){
                        $array = json_decode($content, true);
                        $dist->folders
                        ->map('store')
                        ->each(function($plugin)use(&$array){
                            $array['require']['arcaela/'.$plugin->filename]='dev-master';
                        });
                        $file->setContent(str_replace(['\/','": "'],['/','":"'],json_encode($array, JSON_PRETTY_PRINT)));
                    }
                }
                if($vendors->exist)
                    $vendors->go("arcaela")->linkTo($dist->path, true);
                $dist->back();
            }
            if($dist->find('/npm')&&$site->find('package.json')&&($all||param("npm"))){
                $all=!param('npm');
                $dist->go('npm');
                $file = store($site->and('package.json'));
                $modules = store($site->and('node_modules'));
                if($file->exist){
                    $content = $file->getContent;
                    if(!preg_match("/\@arcaela/",$content)){
                        $array = json_decode($content, true);
                        $dist->folders
                        ->map('store')
                        ->each(function($plugin)use(&$array){
                            $array['dependencies']['@arcaela/'.$plugin->filename]='latest';
                        });
                        $file->setContent(str_replace(['\/','": "'],['/','":"'],json_encode($array, JSON_PRETTY_PRINT)));
                    }
                }
                if($modules->exist)
                    $modules->go("@arcaela")->linkTo($dist->path, true);
                $dist->back();
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