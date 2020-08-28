#!/bin/bash
. $(dirname $(realpath $0))/.env
[[ ! $(which arc) ]] && $(add_bin arc $base_path/arc)




#########################################################
if [[ -f "$script_path/$pcmd" ]]; then
    source $script_path/$pcmd
elif [[ -f "$script_path/${command:2}" ]]; then
    source $script_path/${command:2}
elif [[ -f "$script_path/$namespace" ]]; then
    source "$script_path/$namespace"
else
    echo "$command no existe como comando interno o externo."
fi
#########################################################