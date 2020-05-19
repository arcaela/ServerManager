#!bash

echo "Actualizando el Sistema"
apt-get update && apt-get upgrade

echo "Instalando Herramientas"
apt-get install software-properties-common

echo "Instalacion de PHP: "
add-apt-repository ppa:ondrej/php
apt-get update
apt install php7.4 php-pear php7.4-curl php7.4-dev php7.4-gd php7.4-mbstring php7.4-zip php7.4-mysql php7.4-xml -y

echo "Instalacion de Apache2: "
apt install apache2 -y

echo "Instalacion de MySQL: "
sudo apt install mysql-server -y
sudo mysql_secure_installation
mysql -uroot <<MYSQL_SCRIPT
    ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'root';
    CREATE USER 'arcaela'@'%' IDENTIFIED WITH mysql_native_password BY 'arcaela';
    GRANT ALL PRIVILEGES ON *.* TO 'arcaela'@'%' WITH GRANT OPTION;
    FLUSH PRIVILEGES;
MYSQL_SCRIPT

echo "Creando llaves SSH: "
sshpath="./vm/$USER"
rootsshpath="./vm/root/"
[ -d $sshpath ] || mkdir $sshpath
[ -d $rootsshpath ] || mkdir $rootsshpath
ssh-keygen -f "$sshpath/ssh" -N ""
ssh-keygen -f "$sshpath/ssh" -N ""