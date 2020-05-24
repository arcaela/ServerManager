<?php

require __DIR__."/autoload.php";

if(param('install')){
    $all=true;
    /* Install Composer */        
    if(!param("no-composer")&&(param("composer")||$all)){
        // line("Instalando composer");
        // copy('https://getcomposer.org/installer', $tmp.'/composer-setup.php');
        // $all=false;
    }
    /* Install NodeJs */        
    if(!param("no-node")&&(param("node")||$all)){
        // line("Instalando NodeJs");
        // $node = collect([
            // 'path'=>store('/usr/local/lib/nodejs')->makeHasDir,
        // ]);
        // $VERSION="v12.16.3";
        // $DISTRO="linux-x64";
        // if($node->path){
            // copy("https://nodejs.org/dist/$VERSION/node-$VERSION-$DISTRO.tar.xz","$tmp/node.tar.xz");
            // sudo tar -xJvf node-$VERSION-$DISTRO.tar.xz -C /usr/local/lib/nodejs
            // line((store("/usr/local/lib/nodejs/node-$VERSION-$DISTRO/bin/node")->linkTo('/usr/bin/node')?'Instalado: ':'Error: ').'node');
            // line((store("/usr/local/lib/nodejs/node-$VERSION-$DISTRO/bin/npm")->linkTo('/usr/bin/npm')?'Instalado: ':'Error: ').'npm');
            // line((store("/usr/local/lib/nodejs/node-$VERSION-$DISTRO/bin/npx")->linkTo('/usr/bin/npx')?'Instalado: ':'Error: ').'npx');
        // }
        // else line('No pudimos crear el directorio para NodeJs');
        // $all=false;
    }
    /* Install Laravel */        
    if(!param("no-laravel")&&(param("laravel")||$all)){
        // line("Instalando Laravel");
        // line( Console::run('composer global require laravel/installer') );
    }
    if($all){
        // line(`sudo mysql -uroot <<MYSQL_SCRIPT
            // ALTER USER 'root'@'%' IDENTIFIED WITH mysql_native_password BY 'root';
            // FLUSH PRIVILEGES;
        // MYSQL_SCRIPT>>`);
    }
}

/* Refrescar la lista de Dominios */
if(param('fresh')){
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