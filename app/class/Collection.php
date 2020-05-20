<?php
    class Collection implements ArrayAccess{
        protected $items;
        protected $macros = [];
        protected $natives = [];
        public function __construct($items){ $this->items=$items instanceof Collection?$items->toArray():$items; }
        public function __set($key,$val){ return $this->items[$key]=($val instanceof Collection?$val->toArray():$val); }
        
        public function __get($key){
            $val=$this->items[$key]??null;
            if(is_array($val)) return new static($val);
            return $val;
        }
        public function __toString(){
            if(array_key_exists('__toString',$this->natives))
                return call($this,$this->natives["__toString"]);
            return $this->toJson();
        }

        public function offsetExists($offset) { return array_key_exists($offset,$this->items); }
        public function offsetGet($offset) { return $this->$offset; }
        public function offsetSet($offset, $valor) { return $this->$offset=$valor; }
        public function offsetUnset($offset) {
            try{
                unset($this->$offset);
                unset($this->items[$offset]);
            }catch(\Trhowable $e){ }
        }
        public function __invoke(...$arg){
            if(array_key_exists('__invoke',$this->natives))
                return call($this,$this->natives["__invoke"],...$arg);
            throw "Cant call object has function";
        }
        public function __call($fn,$arg){
            if(array_key_exists("__call", $this->natives))
                return call($this, $this->natives['__call'],...$arg);
            else if(array_key_exists($fn, $this->macros))
                return call($this, $this->macros[$fn],...$arg);
            return call($this, $this->$fn,...$arg);
        }
        public function __items($collect){ $this->items=$collect; return $this; }

        ////////////////////////////////////////////////////////////////////////////////////

        public function native($key,\Closure $fn){ $this->natives[$key]=$fn; return $this; }
        public function every($fn){
            $pop = [];
            foreach($this->items as $key => $value)
                $pop[$key]=call($this, $fn, $value,$key);
            return $pop;
        }
        public function each($fn){ $this->every($fn); return $this; }
        public function map($fn){ $this->items = $this->every($fn);return $this; }
        public function mapWithKeys($fn){
            $pop=[];
            foreach($this->every($fn) as $t) $pop[$t[0]]=$t[1];
            $this->items=$pop;
            return $this;
        }

        public function filter($fn){
            $this->items = array_filter($this->items,$fn);
            return $this;
        }
        public function pop(){ $this->items = array_filter($this->items); return $this; }
        public function macro($name, \Closure $fn){ $this->macros[$name]=$fn; return $this; }
        public function slice(...$slice){ $this->items=array_slice($this->items,...$slice); return $this; }
        public function merge(...$pack){ $this->items=array_merge($this->items,...$pack);return $this; }


        public function keys(){ return array_keys($this->items); }
        public function values(){ return array_values($this->items); }
        public function if($fn){ return call($this,$fn,[])?true:false; }
        public function then($fn){ call($this,$fn); return $this; }


        public function toArray(){
            return array_map(function($item){
                return ($item instanceof static)?$item->toArray():$item;
            },$this->items);
        }
        public function toJson(){ return json_encode($this->toArray(),JSON_PRETTY_PRINT); }
    }