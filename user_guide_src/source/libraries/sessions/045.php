<?php

session()->setFlashdata('alerts', 'Operation success!');

/**
 * Get flash value 'Operation success!' in another controller.
 *
 * echo session()->getFlashdata('alerts');
 */

// Switch flag
session()->markAsTempdata('alerts');

// or rewrite
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
