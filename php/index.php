<?php
require __DIR__."/autoload.php";



listen('scan',function(){
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
    ->map(function($item){ return config()->add($item); })
    ->pop()
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
});


listen('add-links',function(){
    $links = [];
    $resources = store(resources_path());
    $node_modules = $resources->clone('node_modules');
    if($node_modules->exist){   
        $node_modules->folders->each(function($module) use(&$links){
            $links[]="node_modules/$module->basename";
        });
    }
    $vendor = $resources->clone('vendor');
    if($vendor->exist){   
        $vendor->folders->each(function($module) use(&$links){
            $links[]="vendor/$module->basename";
        });
    }
    store(config('path')->pages)
    ->find("*.*")
    ->each(function($site) use(&$resources, $links){
        foreach($links as $path)
            $site->clone("/$path")->linkTo( $resources->and($path), true );
    });
});

listen('info',function(){
    line("###### ServerManager | Powered by Arcaela ######");
    line("Os: ".iOS());
    line("PHP Version: ".phpversion());
    line("TLD: ".config()->tld );
    line("Domains: ".(
        store(config()->path->pages)
        ->find('*.*')
        ->filter(function($info){
            return in_array($info->basename, DOMAIN_LIST??[$info->basename]);
        })->map(function($e){
            return $e->basename;
        })
    ));
});