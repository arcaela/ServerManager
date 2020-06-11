#!/bin/bash
ArcaelaRoot="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"

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
    keypath=$ArcaelaRoot"/shell/ssh/authorized_keys"
    if [[ ! -f "$keypath" ]]; then
        touch $keypath
        echo "authorized_keys : created"
    fi
    sudo nano $keypath
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
        sudo $ArcaelaRoot"/shell/apt-update"
    fi
    if [[ ! -z $update || -z $(which composer) ]]; then
        sudo $ArcaelaRoot"/shell/dist/composer"
    fi
    if [[ ! -z $update || -z $(which node) ]]; then
        sudo $ArcaelaRoot"/shell/dist/nodejs"
    fi
fi



if [[ ! -z $(which php) ]]; then
    sudo php $ArcaelaRoot/app/index.php $context
else
    echo "Se require PHP para utilizar los servicios"
fi