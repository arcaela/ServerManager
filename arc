#!/bin/bash
base_path="$(realpath $( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd ))"
source $base_path/.env

on_views=(
    "--server-install" "--server-upgrade"
)
for cmd in "${on_views[@]}"
do
    if [[ $cmd == $1 ]]; then
        view="${1:2}"
        view="${view[@]::$(echo `expr index "$view" -`)-1}"
        action=$1
        source $base_path/views/$view
        exit
    fi
done


if [[ ! -z $(which php) ]]; then
    _php --server-path=$base_path --public-path=$web_path $arguments
else
    echo "Se require PHP para utilizar los servicios"
fi