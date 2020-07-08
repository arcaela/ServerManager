#!/bin/bash
[[ -z $1 ]] && exit
base_path="$(realpath $( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd ))"
source $base_path/.env

req=$1
req="${req[@]:2:`expr index "${req:2}" -`-1}"
jdbfvzhsdbflab="$(pwd)"
cd $views_path/
files=(*)
cd $jdbfvzhsdbflab/



if [[ ! -z $req && ${files[@]} =~ ${req} ]]; then
    action=$1
    params="${arg_array[@]:1}"
    source $base_path/views/$req
    exit
elif [[ $(which php) ]]; then
    _php --server-path=$base_path --public-path=$web_path $arg
else
    echo "Se require PHP para utilizar los servicios"
fi