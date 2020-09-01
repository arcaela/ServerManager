#!/bin/bash
. $(dirname $(realpath $0))/.env
[[ ! -f "$bin_path/arc" ]] && $(add_bin arc "$base_path/arc")
if [[ ! -f "$bash_file" || $command == '--server-refresh' ]]; then
    $(delete /etc/bash_completion.d/arcaela)
    $(delete $bash_file)
echo "#!/bin/bash
$(parse_export $base_path/bin)
if [[ -d $bash_path ]]; then
    for file in $bash_path/*; do
        "'[[ -f $file ]] && . $file'"
    done
fi
" >> $bash_file
    $(link $bash_file /etc/bash_completion.d/arcaela)
    sudo chmod -R 777 $bash_file
    . $bash_file
fi

#########################################################
if [[ -f "$script_path/$fcmd" ]]; then
    source $script_path/$fcmd
elif [[ -f "$script_path/${command:2}" ]]; then
    source $script_path/${command:2}
elif [[ -f "$script_path/$namespace" ]]; then
    source "$script_path/$namespace"
else
    echo "$command no existe como comando interno o externo."
fi
#########################################################