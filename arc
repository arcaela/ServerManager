#!/bin/bash
source $HOME/.bashrc

base_path="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
context=""
while [[ $# -gt 0 ]]; do
    context="$context $1"
    case $1 in
        --ssh-add)
            workspace="ssh"
            action="add_client"
        ;;
        --server-install)
            workspace="server"
            action="install"
        ;;
    esac
    shift    
done

case $workspace in
    #ssh
    ssh)
        case $action in
            #add_client
            add_client)
                ssh_path=$HOME"/.ssh"
                authorized_keys=$ssh_path"/authorized_keys"
                touch $authorized_keys
                read -p "SSH Key: " ssh_client
                echo $ssh_client >> $authorized_keys
            ;;
            #add_client
        esac
    ;;
    #ssh
    #server
    server)
        case $action in
            install)
                if [[ -z $(which php) ]]; then
                    sudo $base_path"/shell/apt-update"
                fi
                if [[ -z $(which composer) ]]; then
                    sudo $base_path"/shell/dist/composer"
                fi
                if [[ -z $(which node) ]]; then
                    sudo $base_path"/shell/dist/nodejs"
                fi
            ;;
        esac
    ;;
    #server
    #Default
    *)
        if [[ ! -z $(which php) ]]; then
            sudo php $base_path/app/index.php --server-path=$base_path $context
        else
            echo "Se require PHP para utilizar los servicios"
        fi
    ;;
    #Default
esac