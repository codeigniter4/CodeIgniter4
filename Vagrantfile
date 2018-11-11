# -*- mode: ruby -*-
# vi: set ft=ruby :

# https://github.com/hashicorp/vagrant/issues/9442#issuecomment-374785457
unless Vagrant::DEFAULT_SERVER_URL.frozen?
  Vagrant::DEFAULT_SERVER_URL.replace('https://vagrantcloud.com')
end

Vagrant.configure("2") do |config|
  # VM Box
  #config.vm.box = "debian/testing64"
  config.vm.box = "ubuntu/bionic64"

  # Automatic box update checking
  config.vm.box_check_update = true

  # CodeIgniter virtual host
  config.vm.network "forwarded_port", guest: 80, host: 8080, host_ip: "127.0.0.1"
  # Code Coverage virtual host
  config.vm.network "forwarded_port", guest: 8080, host: 8081, host_ip: "127.0.0.1"

  # virtualbox type allow auto-sync host to guest and guest to host
  # VAGRANT_DISABLE_STRICT_DEPENDENCY_ENFORCEMENT=1 vagrant plugin install vagrant-vbguest
  config.vm.synced_folder ".", "/var/www/codeigniter", type: "rsync"

  # Provider-specific configuration
  config.vm.provider "virtualbox" do |vb|
    # Display the VirtualBox GUI when booting the machine
    vb.gui = false
    # Customize the amount of memory on the VM:
    vb.memory = "512"
  end

  # Provision
  config.vm.provision "shell", inline: <<-SHELL
    MYSQL_ROOT_PASS="password"
    VIRTUALHOST="localhost"
    PHP_VERSION=7.2
    PGSQL_VERSION=11

    echo "127.0.0.1 ${VIRTUALHOST}" >> /etc/hosts

    export DEBIAN_FRONTEND=noninteractive

    echo "Updating and installing required packages..."

    apt-get update

    debconf-set-selections <<< "mysql-server mysql-server/root_password password ${MYSQL_ROOT_PASS}"
    debconf-set-selections <<< "mysql-server mysql-server/root_password_again password ${MYSQL_ROOT_PASS}"

    apt-get install -y \
    php$PHP_VERSION apache2 curl composer \
    php-intl php-mbstring php-curl php-gd php-xdebug \
    php-mysql mysql-server mysql-client \
    php-pgsql postgresql-$PGSQL_VERSION postgresql-client-$PGSQL_VERSION \
    php-sqlite3 sqlite3 \
    php-memcached memcached \
    php-redis redis-server

    apt-get autoclean

    echo "Configuring databases..."

    mysql -e "CREATE DATABASE IF NOT EXISTS codeigniter;" -uroot -p$MYSQL_ROOT_PASS
    mysql -e "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = 'codeigniter';" -uroot -p$MYSQL_ROOT_PASS
    mysql -e "GRANT ALL PRIVILEGES ON *.* TO 'root'@'%' IDENTIFIED BY 'root' WITH GRANT OPTION; FLUSH PRIVILEGES;" -uroot -p$MYSQL_ROOT_PASS
    sed -i "s/^bind-address/#bind-address/" /etc/mysql/my.cnf
    systemctl restart mysql


    sed -i "s/#listen_addresses = 'localhost'/listen_addresses = '*'/" /etc/postgresql/$PGSQL_VERSION/main/postgresql.conf
    echo "host    all             all             all                     md5" >> /etc/postgresql/$PGSQL_VERSION/main/pg_hba.conf
    sudo -u postgres psql -c 'CREATE DATABASE codeigniter;'
    sudo -u postgres psql -c "alter user postgres with password 'password';"
    systemctl restart postgresql

    echo "Configuring virtual hosts..."

    mkdir -p /var/www/codeigniter/builds/coverage-html
    mkdir -p /var/www/codeigniter/public
    mkdir -p /var/www/codeigniter/writable/apache

    sed -i "s/APACHE_RUN_USER=www-data/APACHE_RUN_USER=vagrant/" /etc/apache2/envvars
    sed -i "s/APACHE_RUN_GROUP=www-data/APACHE_RUN_GROUP=vagrant/" /etc/apache2/envvars

    echo "
<VirtualHost *:80>
    ServerAdmin webmaster@${VIRTUALHOST}
    ServerName ${VIRTUALHOST}
    ServerAlias www.${VIRTUALHOST}
    DirectoryIndex index.php
    DocumentRoot /var/www/codeigniter/public
    LogLevel warn
    ErrorLog  /var/www/codeigniter/writable/apache/error.log
    CustomLog /var/www/codeigniter/writable/apache/custom.log combined
</VirtualHost>
<VirtualHost *:8080>
    ServerName ${VIRTUALHOST}
    ServerAlias www.${VIRTUALHOST}
    DirectoryIndex index.html
    DocumentRoot /var/www/codeigniter/builds/coverage-html
</VirtualHost>
" > /etc/apache2/sites-available/codeigniter.conf

    a2enmod rewrite
    a2dissite 000-default.conf
    a2ensite codeigniter.conf
    systemctl restart apache2

  SHELL
end
