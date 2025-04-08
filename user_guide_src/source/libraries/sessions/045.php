<?php

session()->setFlashdata('alerts', 'Operation success!');

/**
 * Get flash value 'Operation success!' in another controller.
 *
 * echo session()->getFlashdata('alerts');
 */

// You can switch the session key type from Flashdata to Tempdata like this:
session()->markAsTempdata('alerts');

// Or simply rewrite it directly
session()->setTempdata('alerts', 'Operation success!');

/**
 * Get temp value 'Operation success!' in another controller.
 *
 * echo session()->getTempdata('alerts');
 *
 * But flash value will be empty 'null'.
 *
 * echo session()->getFlashdata('alerts');
 */
