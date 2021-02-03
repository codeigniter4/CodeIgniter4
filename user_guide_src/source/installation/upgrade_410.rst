######################################
Upgrading from 4.0.5 to 4.1.0 or 4.1.1
######################################

**Legacy Autoloading**

`Autoloader::loadLegacy()` method was originally for transition to CodeIgniter v4. Since `4.1.0`,
this support was removed and this method should not be used.

**Model::fillPlaceholders**

Replace any use of this method with its equivalent version from Validation instead.
