#!/bin/bash
while [[ $# -gt 0 ]]; do
    case $1 in
        --server-install)
            install=1
        ;;
        --server-update)
            update=1
        ;;
    esac
    shift    
done

if [[ ! -z $install || ! -z $update ]]; then
    if [[ ! -z $update || -z $(which php) ]]; then
        ./shell/apt-update
    fi
    if [[ ! -z $update || -z $(which composer) ]]; then
        ./shell/dist/composer
    fi
    if [[ ! -z $update || -z $(which node) ]]; then
        ./shell/dist/nodejs
    fi
fi
if [[ ! -z $(which php) ]]; then
    sudo php ./app/index.php $@
else
    echo "Se require PHP para utilizar los servicios"
fi