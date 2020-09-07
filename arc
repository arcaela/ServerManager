#!/bin/bash
. $(dirname $(realpath $0))/.env
. $script_path/autoload

#########################################################
if [[ -f "$script_path/$fcmd" ]]; then
    source $script_path/$fcmd
elif [[ -f "$script_path/${command:2}" ]]; then
    source $script_path/${command:2}
elif [[ -f "$script_path/$namespace" ]]; then
    source "$script_path/$namespace"
else
    echo "El comando no está registrado."
fi
#########################################################