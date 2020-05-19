<?php
if(!function_exists('config')) include __DIR__.'/app/autoload.php';

$root = __DIR__.'/resources/';
$composer_path = $root.'composer/';
$npm_path = $root.'npm/arcaela/';
line();
path(config('path')->pages)
    ->glob('*.*')
    ->each(function($path)use($composer_path,$npm_path){
        $domain = "{$path->filename}.{$path->extension}";
        line("          $domain           ");
        if( $path->exists('composer.json') && (!param('npm') || param('composer'))){
            $path->go('vendor/arcaela');
            if( $path->exists() ) $path->remove();
            line(($path->linkTo($composer_path, true)?"Success: ":"Error: ")."composer");
            $path->back(2);
        }
        if( $path->exists('package.json') && (!param('composer') || param('npm'))){
            $path->go('node_modules/@arcaela');
            if( $path->exists() ) $path->remove();
            line(($path->linkTo($npm_path, true)?"Success: ":"Error: ")."npm");
            $path->back(2);
        }
        if( !$path->exists('composer.json') && !$path->exists('package.json') )
            line("No requiere instalacion");
        line();
    });