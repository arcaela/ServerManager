#!/bin/bash
. $(dirname $(realpath $0))/.env
[[ ! -f "$bin_path/arc" ]] && $(add_bin arc "$base_path/arc")
if [[ ! -f "$base_path/.bashrc" || $command == '--server-refresh' ]]; then
    __dd="$base_path/.bashrc"
    $(delete $_dd)
    echo '#!/bin/bash' >> $__dd
    echo $(parse_export $base_path/bin) >> $__dd
    echo "if [[ -d $bash_path ]]; then" >> $__dd
    echo "    for file in $bash_path/*; do" >> $__dd
    echo '        [[ -f $file ]] && source $file' >> $__dd
    echo '    done' >> $__dd
    echo 'fi' >> $__dd
    sudo chmod -R 777 $__dd
    $(delete /etc/bash_completion.d/arcaela)
    $(link $__dd /etc/bash_completion.d/arcaela)
    . $__dd
fi

#########################################################
if [[ -f "$script_path/$pcmd" ]]; then
    source $script_path/$pcmd
elif [[ -f "$script_path/${command:2}" ]]; then
    source $script_path/${command:2}
elif [[ -f "$script_path/$namespace" ]]; then
    source "$script_path/$namespace"
else
    echo "$command no existe como comando interno o externo."
fi
#########################################################