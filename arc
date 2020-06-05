#!/bin/bash

context=""
while [[ $# -gt 0 ]]; do
    context="$context $1"
    case $1 in
        --ssh-add-client)
            ssh_add_client=1
        ;;
        --server-install)
            install=1
        ;;
        --server-update)
            update=1
        ;;
    esac
    shift    
done

if [[ ! -z $ssh_add_client ]]; then
    if [[ ! -f "$keypath" ]]; then
        touch $keypath
        echo "authorized_keys : created"
    fi
    sudo nano $keypath
    keypath="./shell/ssh/authorized_keys"
    for userDir in /home/* ; do
        if [[ $userDir != '/home/lost+found' ]]; then
            if [[ -d "$userDir/.ssh/" ]]; then
                sudo rm -rf "$userDir/.ssh/authorized_keys"
                sudo cp $keypath "$userDir/.ssh/authorized_keys"
            fi
        fi
    done
    sudo cp $keypath "/root/.ssh/authorized_keys"
    exit
fi




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
    sudo php ./app/index.php $context
else
    echo "Se require PHP para utilizar los servicios"
fi