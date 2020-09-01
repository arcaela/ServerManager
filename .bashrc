#!/bin/bash
export PATH="$PATH:/media/arcaela/HOSTING/bin"
export PATH="$PATH:/home/arcaela/.local/bin"
if [[ -d /media/arcaela/HOSTING/bashrc.d ]]; then
    for file in /media/arcaela/HOSTING/bashrc.d/*; do
        [[ -f $file ]] && source $file
    done
fi
