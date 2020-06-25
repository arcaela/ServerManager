<?php

class Console {
    public static function run($command,$return_var=null){
        if(function_exists('system')) {
            ob_start();
            system($command , $return_var);
            $output = ob_get_contents();
            ob_end_clean();
        }
        else if(function_exists('passthru')) {
            ob_start();
            passthru($command , $return_var);
            $output = ob_get_contents();
            ob_end_clean();
        }
        else if(function_exists('exec')) {
            exec($command , $output , $return_var);
            $output = implode("\n" , $output);
        }
        else if(function_exists('shell_exec'))
            $output = shell_exec($command);
        else {
            $output = 'Command execution not possible on this system';
            $return_var = 1;
        }
        return new class(['output'=>$output,'status'=>$return_var,]){
            public $item;
            public function __construct($data){ $this->item=$data; }
            public function __get($key){ return $this->item[$key]??null;}
            public function __set($key,$val){ return $this->item[$key]=$val;}
            public function __toString() { return $this->item['output']; }
        };
    }


}