#!/bin/bash
[[ -z $1 ]] && exit
base_path="$(realpath $( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd ))"
source $base_path/.env
files=($(scandir $views_path))
################################################################D
if [[ ! -z $namespace && ${files[@]} =~ ${namespace} ]]; then
    action=$cmd
    source $views_path/$namespace
    exit
elif [[ $(which php) ]]; then
    _php --server-path=$base_path --public-path=$web_path $arg
else
    echo "Se require PHP para utilizar los servicios"
fi
################################################################D
