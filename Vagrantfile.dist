# -*- mode: ruby -*-
# vi: set ft=ruby :

# https://github.com/hashicorp/vagrant/issues/9442#issuecomment-374785457
unless Vagrant::DEFAULT_SERVER_URL.frozen?
  Vagrant::DEFAULT_SERVER_URL.replace('https://vagrantcloud.com')
end

Vagrant.configure("2") do |config|
  # VM Box
  config.vm.box = "ubuntu/bionic64"
  # Automatic box update checking
  config.vm.box_check_update = true

  # CodeIgniter virtual host
  config.vm.network "forwarded_port", guest: 80, host: 8080, host_ip: "127.0.0.1"
  # Code Coverage virtual host
  config.vm.network "forwarded_port", guest: 81, host: 8081, host_ip: "127.0.0.1"
  # User Guide virtual host
  config.vm.network "forwarded_port", guest: 82, host: 8082, host_ip: "127.0.0.1"
  # MySQL server
  #config.vm.network "forwarded_port", guest: 3306, host: 3307, host_ip: "127.0.0.1"
  # PostgreSQL server
  #config.vm.network "forwarded_port", guest: 5432, host: 5433, host_ip: "127.0.0.1"
  # Memcached server
  #config.vm.network "forwarded_port", guest: 11211, host: 11212, host_ip: "127.0.0.1"
  # Redis server
  #config.vm.network "forwarded_port", guest: 6379, host: 6380, host_ip: "127.0.0.1"

  # Add "192.168.10.10 ${VIRTUALHOST}" in your host file to access by domain
  #config.vm.network "private_network", ip: "192.168.10.10"

  # Same path set in the $CODEIGNITER_PATH Provision
  # "virtualbox" type allow auto-sync host to guest and guest to host
  # but chmod does not work... tests will fail.
  # Default rsync__args except "--copy-links", to allow phpunit correctly works by symlink
  config.vm.synced_folder ".", "/var/www/codeigniter", type: "rsync", rsync__args: ["--verbose", "--archive", "--delete", "-z"]

  # Provider-specific configuration
  config.vm.provider "virtualbox" do |vb|
    # Display the VirtualBox GUI when booting the machine
    vb.gui = false
    # Customize the amount of memory on the VM:
    vb.memory = "1024"
  end

  # Provision
  config.vm.provision "shell", inline: <<-SHELL
    MYSQL_ROOT_PASS="password"
    PGSQL_ROOT_PASS="password"
    VIRTUALHOST="localhost"
    CODEIGNITER_PATH="/var/www/codeigniter"
    PHP_VERSION=7.2
    PGSQL_VERSION=10
    #APT_PROXY="192.168.10.1:3142"

    grep -q "127.0.0.1 ${VIRTUALHOST}" /etc/hosts || echo "127.0.0.1 ${VIRTUALHOST}" >> /etc/hosts

    # Creates a swap file if necessary
    RAM=`awk '/MemTotal/ {print $2}' /proc/meminfo`
    if [ $RAM -lt 1000000 ] && [ ! -f /swap/swapfile ]; then
        echo "================================================================================"
        echo "Adding swap"
        echo "================================================================================"
        echo "This process may take a few minutes. Please wait..."
        mkdir /swap
        dd if=/dev/zero of=/swap/swapfile bs=1024 count=1000000
        chmod 600 /swap/swapfile
        mkswap /swap/swapfile
        swapon /swap/swapfile
        echo "/swap/swapfile swap swap defaults 0 0" >> /etc/fstab
        echo "Done."
    fi

    # Prepare to use APT Proxy
    if [ ! -z $APT_PROXY ]; then
        if [ ! -f /etc/apt/sources.list-origin ]; then
            cp /etc/apt/sources.list /etc/apt/sources.list-origin
        fi
        sed -i "s/archive.ubuntu.com/${APT_PROXY}/" /etc/apt/sources.list
        sed -i "s/security.ubuntu.com/${APT_PROXY}/" /etc/apt/sources.list
    fi

    export DEBIAN_FRONTEND=noninteractive

    echo "================================================================================"
    echo "Updating and Installing Required Packages"
    echo "================================================================================"

    apt-get update

    debconf-set-selections <<< "mysql-server mysql-server/root_password password ${MYSQL_ROOT_PASS}"
    debconf-set-selections <<< "mysql-server mysql-server/root_password_again password ${MYSQL_ROOT_PASS}"

    apt-get install -y \
    php$PHP_VERSION apache2 composer \
    php-intl php-mbstring php-xml php-zip php-xdebug \
    php-mysql mysql-server mysql-client \
    php-pgsql postgresql-$PGSQL_VERSION \
    php-sqlite3 sqlite3 \
    php-memcached memcached \
    php-redis redis-server \
    php-curl curl \
    php-gd php-imagick \
    python-pip

    pip install sphinx sphinxcontrib-phpdomain

    apt-get autoclean

    echo "================================================================================"
    echo "Preparing User Guide"
    echo "================================================================================"

    cd "${CODEIGNITER_PATH}/user_guide_src/cilexer"
    python setup.py install
    cd ..
    make html

    echo "================================================================================"
    echo "Configuring Databases"
    echo "================================================================================"

    sed -i "s/^bind-address/#bind-address/" /etc/mysql/mysql.conf.d/mysqld.cnf
    mysql -e "CREATE DATABASE IF NOT EXISTS codeigniter COLLATE 'utf8_general_ci';
    UPDATE mysql.user SET Host='%' WHERE user='root';
    GRANT ALL PRIVILEGES ON *.* TO 'root'@'%' WITH GRANT OPTION;
    FLUSH PRIVILEGES;" -uroot -p$MYSQL_ROOT_PASS
    systemctl restart mysql

    sed -i "s/^#listen_addresses = 'localhost'/listen_addresses = '*'/" /etc/postgresql/$PGSQL_VERSION/main/postgresql.conf
    grep -q "host    all             root            all                     md5" /etc/postgresql/$PGSQL_VERSION/main/pg_hba.conf || echo "host    all             root            all                     md5" >> /etc/postgresql/$PGSQL_VERSION/main/pg_hba.conf
    sudo -u postgres psql -tc "SELECT 1 FROM pg_roles WHERE rolname='root'" | grep -q 1 || sudo -u postgres psql -c "CREATE ROLE root WITH SUPERUSER CREATEDB CREATEROLE INHERIT LOGIN"
    sudo -u postgres psql -c "ALTER ROLE root WITH PASSWORD '${PGSQL_ROOT_PASS}'"
    sudo -u postgres psql -tc "SELECT 1 FROM pg_database WHERE datname='codeigniter'" | grep -q 1 ||sudo -u postgres psql -c "CREATE DATABASE codeigniter"
    sudo -u postgres psql -c "GRANT ALL PRIVILEGES ON DATABASE codeigniter TO root"
    systemctl restart postgresql

    echo "================================================================================"
    echo "Configuring Memcached and Redis"
    echo "================================================================================"

    sed -i "s/^bind 127.0.0.1/#bind 127.0.0.1/" /etc/redis/redis.conf
    sed -i "s/^protected-mode yes/protected-mode no/" /etc/redis/redis.conf
    sed -i "s/^-l 127.0.0.1/#-l 127.0.0.1/" /etc/memcached.conf
    systemctl restart redis
    systemctl restart memcached

    echo "================================================================================"
    echo "Configuring Virtual Hosts"
    echo "================================================================================"

    mkdir -p "${CODEIGNITER_PATH}/build/coverage-html"
    mkdir -p "${CODEIGNITER_PATH}/public"
    mkdir -p "${CODEIGNITER_PATH}/user_guide_src/build/html"
    mkdir -p "${CODEIGNITER_PATH}/writable/apache"
    chown -R vagrant:vagrant $CODEIGNITER_PATH

    # Creates a symlink in the user home
    if [ ! -d /home/vagrant/codeigniter ]; then
        ln -s $CODEIGNITER_PATH /home/vagrant/codeigniter
    fi

    sed -i "s/APACHE_RUN_USER=www-data/APACHE_RUN_USER=vagrant/" /etc/apache2/envvars
    sed -i "s/APACHE_RUN_GROUP=www-data/APACHE_RUN_GROUP=vagrant/" /etc/apache2/envvars
    grep -q "Listen 81" /etc/apache2/ports.conf || sed -i "s/^Listen 80/Listen 80\\nListen 81\\nListen 82/" /etc/apache2/ports.conf
    sed -i "s/^display_errors = Off/display_errors = On/" /etc/php/7.2/apache2/php.ini
    sed -i "s/^display_startup_errors = Off/display_startup_errors = On/" /etc/php/7.2/apache2/php.ini

    echo "ServerName ${VIRTUALHOST}
<Directory ${CODEIGNITER_PATH}>
    DirectoryIndex index.html index.php
    Options All
    AllowOverride All
</Directory>
<VirtualHost *:80>
    ServerAdmin vagrant@localhost
    DocumentRoot ${CODEIGNITER_PATH}/public
    ErrorLog  ${CODEIGNITER_PATH}/writable/apache/error.log
    CustomLog ${CODEIGNITER_PATH}/writable/apache/custom.log combined
</VirtualHost>
<VirtualHost *:81>
    DocumentRoot ${CODEIGNITER_PATH}/build/coverage-html
</VirtualHost>
<VirtualHost *:82>
    DocumentRoot ${CODEIGNITER_PATH}/user_guide_src/build/html
</VirtualHost>
" > /etc/apache2/sites-available/codeigniter.conf

    a2enmod rewrite
    a2dissite 000-default.conf
    a2ensite codeigniter.conf
    systemctl restart apache2

    echo "================================================================================"
    echo "Services Status"
    echo "================================================================================"
    service --status-all

  SHELL
end
