#!/bin/bash
. $(dirname $(realpath $0))/.env
#########################################################
if [[ $command == "arc" ]]; then
    command="${@:1:1}"
    arguments=(${@:2})
    source $function_path/$command
elif [[ -f "$commands_path/$command" ]]; then
    source $commands_path/$command
else
    echo "No podemos ejectura: $command"
fi
#########################################################