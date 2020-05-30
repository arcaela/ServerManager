<?php
    function param(...$a){ global $_PARAMS; return $_PARAMS(...$a);}
    function config(...$a){ global $_CONFIGS; return $_CONFIGS(...$a);}
    function template(...$a){
        if(count($a)){
            $merge = (count($a)>1)?(
                is_array($a[1])?$a[1]:(
                    $a[1] instanceof Collection?($a[1])->toArray():[]
                )
            ):[];
            return str_replace(
                array_map(function($k){ return "<$k>";},array_keys($merge)),
                array_values($merge),
                file_get_contents(__DIR__."/template/".$a[0])
            );
        }
        return null;
    }

    function iOS(...$compare){
        $ios = (false
            ||array_key_exists('WINDIR',$_SERVER)
            ||array_key_exists('windir',$_SERVER)
            ||(array_key_exists('HTTP_USER_AGENT',$_SERVER)
                &&preg_match("/windows/i",$_SERVER['HTTP_USER_AGENT']))
        )?'windows':'linux';
        return count($compare)?preg_match("/".$compare[0]."/i",$ios):$ios;
    }
    function collect($items){ return new Collection($items); }



    function store($path=null){
        $path = preg_replace("/^(.*)(\/|\\\)$/","$1", preg_replace("/(\/|\\\)+/",DIRECTORY_SEPARATOR, ($path??'./')) );
        return collect(array_merge(
            ["path"=>$path,],
            pathinfo($path)
        ))
        ->macro('get(real)',function(){
            $real=realpath($this->path);
            return $real?store($real):null;
        })
        ->native('__toString',function(){ return $this->path; })
        ->macro('get(getContent)',function(){
            try {
                return $this->exist?file_get_contents($this->real->path):'';
            } catch (\Throwable $th) { exit($th->getMessage()); }
        })
        ->macro('get(makeHasFile)',function(){
            if(!$this->exist) fclose(fopen($this->path,"a"));
            else if(!$this->isFile) return null;
            return $this;
        })
        ->macro('get(makeHasDir)',function(){
            if(!$this->exist) mkdir($this->path."/", 0777, true);
            else if(!$this->isDir) return null;
            return $this;
        })
        ->macro('setContent',function($content=''){
            if(!$this->exist||$this->isFile){
                try {
                    $prev=$this->isFile?file_get_contents($this->path):'';
                    $fn=fopen($this->path,"r+");
                    ftruncate($fn, 0);
                    fputs($fn, is_callable($content)?bind($this,$content,$prev):$content);
                    fclose($fn);
                } catch (\Throwable $th) {return null;}
            };
            return $this;
        })
        ->macro('get(unlink)',function(){
            try {
                if(is_link($this->path)||is_file($this->path)) unlink($this->path);
                if(is_dir($this->path)){
                    $scan = $this->scandir(true);
                    $scan->files->map(function($p){unlink($p);});
                    $scan->folders->map(function($p){rmdir($p);});
                    rmdir($this->path);
                }
                return $this->exist?"2":$this;
            } catch (\Throwable $th) { return $th; }
        })
        ->macro('and',function($path=''){
            return preg_replace("/\/+/","/",$this->path."/$path");
        })
        ->macro('open',function($path=''){
            return store(preg_replace("/\/+/","/",$this->path."/$path"));
        })
        ->macro('go',function($path=''){
            $path = preg_replace("/\/+/","/",$this->path."/$path");
            return $this->__items(array_merge(
                ["path"=>$path,],
                pathinfo($path)
            ));
        })
        ->macro('back',function($level=1){
            for($i=0;$i<$level;$i++){
                $this->__items(array_merge(
                    ["path"=>$this->dirname,],
                    pathinfo($this->dirname)
                ));
            }
            return $this;
        })
        ->macro('get(files)', function(){ return $this->scandir(1)->files; })
        ->macro('get(folders)', function(){ return $this->scandir(1)->folders; })
        ->macro('scandir',function($MaxLevel=true){
            if(!is_dir($this->path)) return null;
            if(!function_exists('getFilesPath')){
                function getFilesPath($path, $level=[0,true], $append=["files"=>[],"folders"=>[]]){
                    $allow = ($level[1]===true||$level[0]<$level[1]);
                    if(!$allow) return $append; $level[0]++;
                    if((!is_link($path) && !is_file($path)) && is_dir($path)){
                        foreach(array_diff(scandir($path), ['.','..']) as $dir){
                            $root = "$path/$dir";
                            if(is_link($root) || is_file($root)) $append["files"][]=$root;
                            else if(is_dir($root)){
                                array_unshift($append["folders"], "$root");
                                $append = getFilesPath("$root",$level,$append);
                            };
                        }
                    }
                    return $append;
                };
            }
            return collect(getFilesPath($this->path, [0,$MaxLevel]));
        })
        ->macro('find', function($exp='*'){
            return collect(glob($this->path."/$exp"))->map("store");
        })
        ->macro('get(isLink)',function(){return is_link($this->path);})
        ->macro('get(isFile)',function(){return is_file($this->path);})
        ->macro('get(isDir)',function(){return is_dir($this->path);})
        ->macro('get(exist)',function(){return boolval($this->real);})
        ->macro('linkTo',function($destino,$overwrite=false){
            try {
                if($this->exist&&$overwrite) $this->error = $this->unlink;
                if($this->exist) return null;
		$this->makeHasDir; $this->unlink;
                symlink($destino, $this->path);
                return $this;
            } catch (\Throwable $th) {}
            return null;
        })
    ;}
    function bind($target,$fn,...$arg){
        return  is_string($fn)?$fn(...$arg):($fn->bindTo($target,$target))(...$arg);
    }
    function line($text=''){ echo (is_array($text)?json_encode($text, JSON_PRETTY_PRINT):$text)."\n"; }
    function fatal($text=''){ throw new Exception($text); }
