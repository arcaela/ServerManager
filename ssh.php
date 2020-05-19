<?php
require __DIR__.'/app/autoload.php';

# echo "Creando llaves SSH: "
$user = param("u")??param("user")??get_current_user();
$path = path(__DIR__.'/vm/'.$user);
$revoke = $revoke=param('revoke');

if( (param("gen")??param("generate")) || $revoke ){
    if($revoke) $path->remove();
    if($path->real){
        line("El directorio ya existe puede probar con: ");
        line("php ".basename(__FILE__)." [-n | --generate] [-r | --revoke]");
        exit;
    }
    $path->create();
    echo Console::run("ssh-keygen -f $path/ssh -N ''");
    exit;
}
else{
    line( file_get_contents("$path/ssh.pub") );
}