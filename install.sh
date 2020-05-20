#!bash

echo "Actualizando el Sistema"
apt-get update -y && apt-get upgrade -y

echo "Instalando Herramientas"
apt-get install software-properties-common -y

echo "Instalacion de Apache2: "
apt install apache2 -y

echo "Instalacion de PHP: "
add-apt-repository ppa:ondrej/php -y
apt-get update
apt install php7.4 php-pear php7.4-curl php7.4-dev php7.4-gd php7.4-mbstring php7.4-zip php7.4-mysql php7.4-xml -y

echo "Instalacion de MySQL: "
sudo apt install mysql-server -y
sudo mysql_secure_installation
sudo mysql -uroot <<MYSQL_SCRIPT
    ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'root';
    FLUSH PRIVILEGES;
MYSQL_SCRIPT

echo "Instalacion de Composer: "
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer
php -r "unlink('composer-setup.php');"
if [ -d "$HOME/.config/composer/vendor/bin" ]
then
    echo 'export PATH="$PATH:$HOME/.config/composer/vendor/bin"' >> ~/.bashrc
else if [ -d "$HOME/.composer/vendor/bin" ]
then
    echo 'export PATH="$PATH:$HOME/.composer/vendor/bin"' >> ~/.bashrc
fi
source ~/.bashrc

echo "Instalacion de NodeJs: "
VERSION=v12.16.3
DISTRO=linux-x64
wget https://nodejs.org/dist/$VERSION/node-$VERSION-$DISTRO.tar.xz
sudo mkdir -p /usr/local/lib/nodejs
sudo tar -xJvf node-$VERSION-$DISTRO.tar.xz -C /usr/local/lib/nodejs
sudo rm -rf ./node-$VERSION-$DISTRO.tar.xz
sudo ln -s /usr/local/lib/nodejs/node-$VERSION-$DISTRO/bin/node /usr/bin/node
sudo ln -s /usr/local/lib/nodejs/node-$VERSION-$DISTRO/bin/npm /usr/bin/npm
sudo ln -s /usr/local/lib/nodejs/node-$VERSION-$DISTRO/bin/npx /usr/bin/npx

echo "Instalacion de Laravel: "
composer global require laravel/installer
