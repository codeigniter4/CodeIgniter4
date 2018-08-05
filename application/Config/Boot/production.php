<?php
declare(strict_types=1);
/*
|--------------------------------------------------------------------------
| ERROR DISPLAY
|--------------------------------------------------------------------------
| Don't show ANY in production environments. Instead, let the system catch
| it and display a generic error message.
*/

// MODIFED TO PREVENT KINT BEING LOADED
if(0):
  ini_set('display_errors', 0);
  error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
else:
  ini_set('display_errors', 'TRUE');
  error_reporting(-1);
endif;  

/*
|--------------------------------------------------------------------------
| DEBUG MODE
|--------------------------------------------------------------------------
| Debug mode is an experimental flag that can allow changes throughout
| the system. It's not widely used currently, and may not survive
| release of the framework.
*/

define('CI_DEBUG', 0);
