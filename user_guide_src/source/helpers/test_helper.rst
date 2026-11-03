###########
Test Helper
###########

The Test Helper file contains functions that assist in testing your project.

.. contents::
    :local:
    :depth: 2

Loading this Helper
===================

This helper is loaded using the following code:

.. literalinclude:: test_helper/001.php

Available Functions
===================

The following functions are available:

.. php:function:: fake($model[, ?array $overrides = null[, $persist = true]])

    :param    Model|object|string    $model: Instance or name of the model to use with Fabricator
    :param    array|null $overrides: Overriding data to pass to ``Fabricator::setOverrides()``
    :param    bool $persist: Whether to save the generated item to the database
    :returns:    A random fake item, saved to the database if ``$persist`` is true
    :rtype:    object|array

    Uses ``CodeIgniter\Test\Fabricator`` to create a random item and optionally add it to the database.

    Usage example:

    .. literalinclude:: test_helper/002.php
