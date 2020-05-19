<?php

// Setting arguments of console
$_PARAMS = collect($_SERVER['argv'])->slice(1)
->mapWithKeys(function($v){
    $ex = explode('=',$v);
    return [
        preg_replace("/^\-+(\w+)/","$1",$ex[0]),
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
$_CONFIGS = collect(path(__DIR__.'/ios/')->files()->files)->mapWithKeys(function($path){
    $key = preg_replace("/.*\/(\w+)\.(\w+)$/","$1",$path);
    $val = include $path;
    return [ $key, $val, ];
})
->then(function(){ $this->active = iOS(); })
->native('__invoke',function(...$key){
    if(!count($key)) return $this[$this->active];
    return $this[$this->active][$key[0]];
});


define('IS_SSL',param('ssl'));
define('DOMAIN_LIST',(function(){
    $d = array_filter(explode(',',param('domains')));
    return count($d)?$d:null;
})());
