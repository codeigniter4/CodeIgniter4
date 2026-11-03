# Upgrading from 4.4.5 to 4.4.6

Please refer to the upgrade instructions corresponding to your installation method.

- [Composer Installation App Starter Upgrading](installing_composer.md#upgrading)
- [Composer Installation Adding CodeIgniter4 to an Existing Project Upgrading](installing_composer.md#upgrading_1)
- [Manual Installation Upgrading](installing_manual.md#upgrading)

## Breaking Changes

### Time::createFromTimestamp() Timezone Change

When you do not specify a timezone, now [Time::createFromTimestamp()](../libraries/time.md#createfromtimestamp) returns a Time instance with the app's timezone.

If you want to keep the timezone UTC, you need to call `setTimezone('UTC')`:

```
use CodeIgniter\I18n\Time;

$time = Time::createFromTimestamp(1501821586)->setTimezone('UTC');
```

## Project Files

Some files in the **project space** (root, app, public, writable) received updates. Due to these files being outside of the **system** scope, they will not be changed without your intervention.

There are some third-party CodeIgniter modules available to assist with merging changes to the project space: [Explore on Packagist](https://packagist.org/explore/?query=codeigniter4%20updates).

### All Changes

This is a list of all files in the **project space** that received changes; many will be simple comments or formatting that have no effect on the runtime:

- **app/Config/App.php**
- **app/Config/Routing.php**
- **app/Views/welcome_message.php**
- **composer.json**
