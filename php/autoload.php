<?php
spl_autoload_register(function($class){
    if(file_exists(__DIR__."/class/$class.php"))
        include __DIR__."/class/$class.php";
});
include __DIR__.'/miscellaneous.php';


// Setting arguments of console
$_PARAMS = collect($_SERVER['argv'])->slice(1)
->mapWithKeys(function($v){
    $ex = explode('=',$v);
    return [
        preg_replace("/^-([a-zA-Z])$|--(\w{2,})/","$1$2",$ex[0]),
        (count($ex)>1?(
            $ex[1]=='true'?true:(
                ($ex[1]==''||$ex[1]=='false')?false:(
                    $ex[1]=='null'?null:(
                        $ex[1]
                    )
                )
            )
        ):true)
    ];
})
->macro('has',function($index){
    return array_key_exists($index, $this->items);
})
->native('__invoke',function(...$a){
    $len = count($a);
    if($len==0) return $this;
    else if($len==1){
        $val = $a[0];
        if(is_string($val)) return $this->$val;
        else if(is_array($val)) return $this->filter(function($v,$key)use($val){ return in_array($key,$val); });
    }
    else if($len>1){
        $key = $a[0]; $val = $a[1];
        if(is_string($key)) return $this->items[$key]=$val;
    }
});
// Set $CONFIGS from iOS file
$_CONFIGS = store(__DIR__.'/config')
->files
->mapWithKeys(function($path){
    $key=preg_replace("/.*\/(\w+)\.(\w+)$/","$1",$path);
    $val=include $path;
    return [ $key, $val, ];
})
->then(function(){$this->active = iOS();})
->native('__invoke',function(...$key){
    $len=count($key);
    if($len==1) return $this[$this->active][$key[0]];
    if($len>=2) return $this->items[$this->active][$key[0]]=$key[1];
    return $this[$this->active];
});
define('IS_SSL',param('ssl'));
define('DOMAIN_LIST',(function(){
    $d = array_filter(explode(',',param('domains')));
    return count($d)?$d:null;
})());
