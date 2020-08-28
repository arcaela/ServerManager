#!/bin/bash
base_path="$(dirname $(realpath ${BASH_SOURCE[0]}))"
source $base_path/.env
if [[ ! $(which arc) ]]; then
    $(add_bin arc $base_path/arc)
fi
if [[ -z $command ]]; then
    echo "Ningún argumento enviado"
    exit
fi

#########################################################
if [[ -f "$script_path/${command:2}" ]]; then
    source $script_path/${command:2}
    exit
fi
namespace="${command:2:`expr index "${command:2}" -`-1}"
if [[ -f "$script_path/$namespace" ]]; then
    source "$script_path/$namespace"
    exit
fi
#########################################################