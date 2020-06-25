#!/bin/bash
base_path="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
source $base_path/.env

case $1 in
    --react-app | --react-project | --laravel-app)
        build app
    ;;
    --server-upgrade)
        build server
    ;;
    # default:
    *)
        if [[ ! -z $(which php) ]]; then
            _php --server-path=$base_path --public-path=$web_path $arguments
        else
            echo "Se require PHP para utilizar los servicios"
        fi
    ;;
esac