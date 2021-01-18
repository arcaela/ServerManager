#!/bin/bash
. $(dirname $(realpath $0))/.env
# Exportar comando global
bash_file="$base_path/basharc"
if [[ ! -f "$bash_file" ]]; then
$(delete $bash_file)
$(delete /etc/bash_completion.d/arcaela)
sudo echo "#!/bin/bash
export PATH=$bin_path:\$PATH
if [[ -d $bash_path ]]; then
    for file in $bash_path/*; do
        "'[[ -f $file ]] && . $file'"
    done
fi" >> $bash_file
$(link $bash_file /etc/bash_completion.d/arcaela)
sudo chmod -R 777 $bash_file
. $bash_file
fi
[[ ! -f "$bin_path/arc" ]] && $(add_bin arc "$base_path/arc")


exit
#########################################################
if [[ $command == "arc" ]]; then
    command="${@:1:1}"
    arguments=(${@:2})
    source $functions_path/$command
elif [[ -f "$commands_path/$command" ]]; then
    source $commands_path/$command
else
    echo "No podemos ejectura: $command"
fi
#########################################################