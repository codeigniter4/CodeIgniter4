**************************
Replacing Common Functions
**************************

There are quite a few functions necessary to CodeIgniter that need to be loaded early for use in the core classes and
thus cannot be placed into a helper. While most users will never have any need to do this, but the option to replace
these functions does exist for those who would like to significantly alter the CodeIgniter core. In the ``App\``
directory there is a file ``Common.php``, and any functions defined in there will take precedence over the versions
found in ``system/Common.php``. This is also an opportunity to create globally-available functions you intend to
use throughout the framework.

.. note:: Messing with a core system class has a lot of implications, so make sure you know what you are doing before
    attempting it.
