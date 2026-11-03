# Running Your App

A CodeIgniter 4 app can be run in a number of different ways: hosted on a web server, using virtualization, or using CodeIgniter's command line tool for testing. This section addresses how to use each technique, and explains some of the pros and cons of them.

!!! important "Important"
    You should always be careful about the case of filenames. Many developers develop on case-insensitive file systems on Windows or macOS. However, most server environments use case-sensitive file systems. If the file name case is incorrect, code that works locally will not work on the server.

If you're new to CodeIgniter, please read the [Getting Started](../intro/index.md) section of the User Guide to begin learning how to build dynamic PHP applications. Enjoy!

## Initial Configuration

### Configure for Your Site URIs

Open the **app/Config/App.php** file with a text editor.

1.  `$baseURL`  
    Set your base URL in `$baseURL`. If you need more flexibility, the baseURL may be set within the [.env](../general/configuration.md#dotenv-file) file as `app.baseURL = 'http://example.com/'`. **Always use a trailing slash on your base URL!**

    !!! note "Note"
        If you don't set the `$baseURL` correctly, in development mode, the debug toolbar may not load properly and web pages may take considerably longer to display.

2.  `$indexPage`  
    If you don't want to include **index.php** in your site URIs, set `$indexPage` to `''`. The setting will be used when the framework generates your site URIs.

    !!! note "Note"
        You may need to configure your web server to access your site with a URL that does not contain **index.php**. See [CodeIgniter URLs](../general/urls.md#removing-the-indexphp-file).

### Configure Database Connection Settings

If you intend to use a database, open the **app/Config/Database.php** file with a text editor and set your database settings. Alternatively, these could be set in your **.env** file. See [Database Configuration](../database/configuration.md#description-of-values) for details.

### Set to Development Mode

If the site is not on the production server, set `CI_ENVIRONMENT` to `development` in the **.env** file to take advantage of the debugging tools provided. See [Setting Development Mode](../guides/first-app/index.md#setting-development-mode) for details.

!!! important "Important"
    In production environments, you should disable error display and any other development-only functionality. In CodeIgniter, this can be done by setting the environment to "production". By default, the application will run using the "production" environment. See also [The ENVIRONMENT Constant](../general/environments.md#the-environment-constant).

### Set Writable Folder Permission

If you will be running your site using a web server (e.g., Apache or nginx), you will need to modify the permissions for the **writable** folder inside your project, so that it is writable by the user or account used by your web server.

### Checking PHP ini Settings

!!! success "Available from version 4.5.0"

[PHP ini settings](https://www.php.net/manual/en/ini.list.php) change the behavior of PHP. CodeIgniter provides a command to check important PHP settings.

```console
php spark phpini:check
```

The *Recommended* column shows the recommended values for production environments. They may differ in development environments.

!!! note "Note"
    If you cannot use the spark command, you can use `CheckPhpIni::run(false)` in your controller.

    E.g.,

    ```php
    <?php

    namespace App\Controllers;

    use CodeIgniter\Security\CheckPhpIni;

    class Home extends BaseController
    {
        public function index(): string
        {
            return CheckPhpIni::run(false);
        }
    }
    ```

## Local Development Server

CodeIgniter 4 comes with a local development server, leveraging PHP's built-in web server with CodeIgniter routing. You can launch it with the following command in the main directory:

```console
php spark serve
```

This will launch the server and you can now view your application in your browser at <http://localhost:8080>.

!!! note "Note"
    The built-in development server should only be used on local development machines. It should NEVER be used on a production server.

If you need to run the site on a host other than simply localhost, you'll first need to add the host to your **hosts** file. The exact location of the file varies in each of the main operating systems, though all unix-type systems (including macOS) will typically keep the file at **/etc/hosts**.

The local development server can be customized with three command line options:

- You can use the `--host` CLI option to specify a different host to run the application at:

    ```console
    php spark serve --host example.dev
    ```

- By default, the server runs on port 8080 but you might have more than one site running, or already have another application using that port. You can use the `--port` CLI option to specify a different one:

    ```console
    php spark serve --port 8081
    ```

- You can also choose a specific version of PHP to use, with the `--php` CLI option, with its value set to the path of the PHP executable you want to use:

    ```console
    php spark serve --php /usr/bin/php8.2
    ```

## Worker Mode with FrankenPHP

!!! success "Available from version 4.7.0"

!!! important "Important"
    Worker Mode is currently **experimental**. The only officially supported worker implementation is **FrankenPHP**, which is backed by the PHP Foundation.

FrankenPHP is a modern PHP application server that supports Worker Mode, allowing your application to handle multiple requests within the same PHP process for improved performance.

### Quick Start

1.  Install FrankenPHP using [static binaries](https://github.com/php/frankenphp/releases)

2.  Generate worker mode files:

    ```console
    php spark worker:install
    ```

3.  Start the server:

    ```console
    frankenphp run
    ```

For detailed configuration, performance tuning, and important considerations about state management, see [Worker Mode](worker_mode.md).

## Hosting with Apache

A CodeIgniter4 webapp is normally hosted on a web server. Apache HTTP Server is the "standard" platform, and is assumed in much of our documentation.

Apache is bundled with many platforms, but can also be downloaded in a bundle with a database engine and PHP from [Bitnami](https://bitnami.com/stack/lamp).

### Configure Main Config File

#### Enabling mod_rewrite

The "mod_rewrite" module enables URLs without "**index.php**" in them, and is assumed in our user guide.

Make sure that the rewrite module is enabled (uncommented) in the main configuration file, e.g., **apache2/conf/httpd.conf**:

```apache
LoadModule rewrite_module modules/mod_rewrite.so
```

#### Setting Document Root

Also make sure that the default document root's `<Directory>` element enables this too, in the `AllowOverride` setting:

```apache
<Directory "/opt/lamp/apache2/htdocs">
    Options Indexes FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>
```

### Hosting with VirtualHost

We recommend using "virtual hosting" to run your apps. You can set up different aliases for each of the apps you work on.

#### Enabling vhost_alias_module

Make sure that the virtual hosting module is enabled (uncommented) in the main configuration file, e.g., **apache2/conf/httpd.conf**:

```apache
LoadModule vhost_alias_module modules/mod_vhost_alias.so
```

#### Adding Host Alias

Add a host alias in your "hosts" file, typically **/etc/hosts** on unix-type platforms, or **c:\Windows\System32\drivers\etc\hosts** on Windows.

Add a line to the file. This could be `myproject.local` or `myproject.test`, for instance:

```
127.0.0.1 myproject.local
```

#### Setting VirtualHost

Add a `<VirtualHost>` element for your webapp inside the virtual hosting configuration, e.g., **apache2/conf/extra/httpd-vhost.conf**:

```apache
<VirtualHost *:80>
    DocumentRoot "/opt/lamp/apache2/myproject/public"
    ServerName   myproject.local
    ErrorLog     "logs/myproject-error_log"
    CustomLog    "logs/myproject-access_log" common

    <Directory "/opt/lamp/apache2/myproject/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

The above configuration assumes the project folder is located as follows:

```text
apache2/
   ├── myproject/      (Project Folder)
   │      └── public/  (DocumentRoot for myproject.local)
   └── htdocs/
```

Restart Apache.

#### Testing

With the above configuration, your webapp would be accessed with the URL **http://myproject.local/** in your browser.

Apache needs to be restarted whenever you change its configuration.

### Hosting with Subfolder

If you want a baseURL like **http://localhost/myproject/** with a subfolder, there are three ways.

#### Making Symlink

Place your project folder as follows, where **htdocs** is the Apache document root:

```
├── myproject/ (project folder)
│      └── public/
└── htdocs/
```

Navigate to the **htdocs** folder and create a symbolic link as follows:

```console
cd htdocs/
ln -s ../myproject/public/ myproject
```

#### Using Alias

Place your project folder as follows, where **htdocs** is the Apache document root:

```
├── myproject/ (project folder)
│      └── public/
└── htdocs/
```

Add the following in the main configuration file, e.g., **apache2/conf/httpd.conf**:

```apache
Alias /myproject /opt/lamp/apache2/myproject/public
<Directory "/opt/lamp/apache2/myproject/public">
    AllowOverride All
    Require all granted
</Directory>
```

Restart Apache.

#### Adding .htaccess

The last resort is to add **.htaccess** to the project root.

It is not recommended that you place the project folder in the document root. However, if you have no other choice, like on a shared server, you can use this.

Place your project folder as follows, where **htdocs** is the Apache document root, and create the **.htaccess** file:

```
└── htdocs/
    └── myproject/ (project folder)
        ├── .htaccess
        └── public/
```

And edit **.htaccess** as follows:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>

<FilesMatch "^\.">
    Require all denied
    Satisfy All
</FilesMatch>
```

And remove the redirect settings in **public/.htaccess**:

```diff
--- a/public/.htaccess
+++ b/public/.htaccess
@@ -16,16 +16,6 @@ Options -Indexes
    # http://httpd.apache.org/docs/current/mod/mod_rewrite.html#rewritebase
    # RewriteBase /

-   # Redirect Trailing Slashes...
-   RewriteCond %{REQUEST_FILENAME} !-d
-   RewriteCond %{REQUEST_URI} (.+)/$
-   RewriteRule ^ %1 [L,R=301]
-
-   # Rewrite "www.example.com -> example.com"
-   RewriteCond %{HTTPS} !=on
-   RewriteCond %{HTTP_HOST} ^www\.(.+)$ [NC]
-   RewriteRule ^ http://%1%{REQUEST_URI} [R=301,L]
-
    # Checks to see if the user is attempting to access a valid file,
    # such as an image or css document, if this isn't true it sends the
    # request to the front controller, index.php
```

### Hosting with mod_userdir (Shared Hosts)

A common practice in shared hosting environments is to use the Apache module "mod_userdir" to enable per-user Virtual Hosts automatically. Additional configuration is required to allow CodeIgniter4 to be run from these per-user directories.

The following assumes that the server is already configured for mod_userdir. A guide to enabling this module is available [in the Apache documentation](https://httpd.apache.org/docs/2.4/howto/public_html.html).

Because CodeIgniter4 expects the server to find the framework front controller at **public/index.php** by default, you must specify this location as an alternative to search for the request (even if CodeIgniter4 is installed within the per-user web directory).

The default user web directory **~/public_html** is specified by the `UserDir` directive, typically in **apache2/mods-available/userdir.conf** or **apache2/conf/extra/httpd-userdir.conf**:

```apache
UserDir public_html
```

So you will need to configure Apache to look for CodeIgniter's public directory first before trying to serve the default:

```apache
UserDir "public_html/public" "public_html"
```

Be sure to specify options and permissions for the CodeIgniter public directory as well. A **userdir.conf** might look like:

```apache
<IfModule mod_userdir.c>
    UserDir "public_html/public" "public_html"
    UserDir disabled root

    <Directory /home/*/public_html>
        AllowOverride All
        Options MultiViews Indexes FollowSymLinks
        <Limit GET POST OPTIONS>
            # Apache <= 2.2:
            # Order allow,deny
            # Allow from all

            # Apache >= 2.4:
            Require all granted
        </Limit>
        <LimitExcept GET POST OPTIONS>
            # Apache <= 2.2:
            # Order deny,allow
            # Deny from all

            # Apache >= 2.4:
            Require all denied
        </LimitExcept>
    </Directory>

    <Directory /home/*/public_html/public>
        AllowOverride All
        Options MultiViews Indexes FollowSymLinks
        <Limit GET POST OPTIONS>
            # Apache <= 2.2:
            # Order allow,deny
            # Allow from all

            # Apache >= 2.4:
            Require all granted
        </Limit>
        <LimitExcept GET POST OPTIONS>
            # Apache <= 2.2:
            # Order deny,allow
            # Deny from all

            # Apache >= 2.4:
            Require all denied
        </LimitExcept>
    </Directory>
</IfModule>
```

### Removing the index.php

See [CodeIgniter URLs](../general/urls.md#apache-web-server).

### Setting Environment

See [Handling Multiple Environments](../general/environments.md#apache).

## Hosting with nginx

nginx is the second most widely used HTTP server for web hosting. Here you can find an example configuration using PHP 8.2 FPM (unix sockets) under Ubuntu Server.

### default.conf

This configuration enables URLs without "**index.php**" in them and uses CodeIgniter's "404 - File Not Found" for URLs ending with ".php".

```nginx
server {
    listen 80;
    listen [::]:80;

    server_name example.com;

    root  /var/www/example.com/public;
    index index.php index.html index.htm;

    location / {
        try_files $uri $uri/ /index.php$is_args$args;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;

        # With php-fpm:
    fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        # With php-cgi:
        # fastcgi_pass 127.0.0.1:9000;
    }

    error_page 404 /index.php;

    # deny access to hidden files such as .htaccess
    location ~ /\. {
        deny all;
    }
}
```

### Setting Environment

See [Handling Multiple Environments](../general/environments.md#nginx).

## Deployment to Shared Hosting Services

See [Deployment](deployment.md#deployment-to-shared-hosting-services).

## Bootstrapping the App

In some scenarios, you will want to load the framework without actually running the whole application. This is particularly useful for unit testing your project, but may also be handy for using third-party tools to analyze and modify your code. The framework comes with two separate bootstrap scripts specifically for these scenarios:

- **system/Test/bootstrap.php**: This script is used primarily for unit testing.
- **system/util_bootstrap.php**: This script is used for other scripts that need access to the framework. It is recommended for use in scripts that are not part of testing, as this will NOT fail gracefully if an exception is thrown.

Most of the paths to your project are defined during the bootstrap process. You may use pre-defined constants to override these, but when using the defaults be sure that your paths align with the expected directory structure for your installation method.
