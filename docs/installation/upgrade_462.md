# Upgrading from 4.6.1 to 4.6.2

Please refer to the upgrade instructions corresponding to your installation method.

- [Composer Installation App Starter Upgrading](installing_composer.md#upgrading)
- [Composer Installation Adding CodeIgniter4 to an Existing Project Upgrading](installing_composer.md#upgrading_1)
- [Manual Installation Upgrading](installing_manual.md#upgrading)

## Project Files

Some files in the **project space** (root, app, public, writable) received updates. Due to these files being outside of the **system** scope, they will not be changed without your intervention.

!!! note "Note"
    There are some third-party CodeIgniter modules available to assist with merging changes to the project space: [Explore on Packagist](https://packagist.org/explore/?query=codeigniter4%20updates).

### Content Changes

The following files received significant changes (including deprecations or visual adjustments) and it is recommended that you merge the updated versions with your application:

#### Config

- **app/Config/Autoload.php**
- **app/Config/Cache.php**
- **app/Config/Cookie.php**
- **app/Config/DocTypes.php**
- **app/Config/Logger.php**
- **app/Config/Mimes.php**
- **app/Config/Modules.php**
- **app/Config/Optimize.php**
- **app/Config/Paths.php**

### All Changes

This is a list of all files in the **project space** that received changes; many will be simple comments or formatting that have no effect on the runtime:

- **app/Config/Autoload.php**
- **app/Config/Cache.php**
- **app/Config/Cookie.php**
- **app/Config/DocTypes.php**
- **app/Config/Logger.php**
- **app/Config/Mimes.php**
- **app/Config/Modules.php**
- **app/Config/Optimize.php**
- **app/Config/Paths.php**
- **app/Views/errors/html/debug.css**
- **app/Views/errors/html/error_exception.php**
- **preload.php**
- **public/index.php**
- spark
