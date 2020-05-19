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

    function path($path){
        $path = preg_replace("/^(.*)(\/|\\\)$/","$1", preg_replace("/(\/|\\\)+/",DIRECTORY_SEPARATOR, $path) );
        $real = realpath($path);
        return collect([
            "path"=>$path,
            "real"=>!$real?null:array_merge([
                "path"=>$real,
            ],pathinfo($real)),
        ])
        ->merge(pathinfo($path))
        ->native('__toString',function(){ return $this->path; })
        ->macro('go',function($path=''){
            return $this->__items(
                path($this->path."/$path")->toArray()
            );
        })
        ->macro('back',function($level=1){
            for($i=0;$i<$level;$i++)
                $this->__items( path($this->dirname)->toArray() );
            return $this;
        })
        ->macro('glob',function($exp='*'){
            return collect(array_map('path',glob($this->path."/$exp")));
        })
        ->macro('remove',function(){
            try {
                if(is_file($this->path) || is_link($this->path)) unlink($this->path);
                if(is_dir($this->path)){
                    $data = $this->files(true);
                    array_map('unlink', $data->files->toArray());
                    array_map('rmdir', $data->dir->toArray());
                    rmdir($this->path);
                }
                return $this->go();
            } catch (\Throwable $th) { return null; }
        })
        ->macro('files',function($MaxLevel=true){
            if(!function_exists('getFilesPath')){
                function getFilesPath($path, $level=[0,true], $append=["files"=>[],"dir"=>[]]){
                    $allow = ($level[1]===true||$level[0]<$level[1]);
                    if(!$allow) return $append; $level[0]++;
                    if((!is_link($path) && !is_file($path)) && is_dir($path)){
                        foreach(array_diff(scandir($path), ['.','..']) as $dir){
                            $root = "$path/$dir";
                            if(is_link($root) || is_file($root)) $append["files"][]=$root;
                            else if(is_dir($root)){
                                array_unshift($append["dir"], "$root");
                                $append = getFilesPath("$root",$level,$append);
                            };
                        }
                    }
                    return $append;
                };
            }
            return collect(getFilesPath($this->path, [0,$MaxLevel]));
        })
        ->macro('create',function($mkdir=''){
            if(!is_dir($this->path) && !file_exists($this->path)){
                try {
                    mkdir($this->path."/$mkdir", 0777, true);
                    return $this->go();
                } catch (\Throwable $th) {}
            }
            return null;
        })
        ->macro('isFile',function($exp=''){ return (is_file($this->path."/$exp")&&!is_link($this->path."/$exp")); })
        ->macro('isLink',function($exp=''){ return (is_link($this->path."/$exp")&&!is_file($this->path."/$exp")); })
        ->macro('isDir',function($exp=''){ return (!is_link($this->path."/$exp")&&!is_file($this->path."/$exp")&&is_dir($this->path)); })
        ->macro('exists',function($exp=''){ return count(glob($this->path."/$exp")); })
        ->macro('linkTo',function($destino,$overwrite=false){
            if($overwrite) $this->remove();
            try {
                if(is_file($this->path) || is_link($this->path)) return null;
                if(!is_dir($this->dirname)) mkdir($this->dirname, 0777, true);
                $symb = symlink($destino, $this->path);
                return $this->go();
            } catch (\Throwable $th) { echo $th->getMessage(); }
        });
    }

    function call(...$arg){ return bind(...$arg); }
    function bind($target,$fn,...$arg){ return ($fn->bindTo($target,$target))(...$arg); }
    function line($text=''){ echo $text."\n"; }
    function fatal($text=''){ throw new Exception($text); }