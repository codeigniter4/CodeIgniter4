###########
Date Helper
###########

The Date Helper file contains functions that assist in working with
dates.

.. contents::
  :local:

.. raw:: html

  <div class="custom-index container"></div>

Loading this Helper
===================

This helper is loaded using the following code::

	helper('date');

Available Functions
===================

The following functions are available:

.. php:function:: now([$timezone = NULL])

	:param	string	$timezone: Timezone
	:returns:	UNIX timestamp
	:rtype:	int

	Returns the current time as a UNIX timestamp, referenced either to your server's
	local time or any PHP supported timezone, based on the "time reference" setting
	in your config file. If you do not intend to set your master time reference to
	any other PHP supported timezone (which you'll typically do if you run a site
	that lets each user set their own timezone settings) there is no benefit to using
	this function over PHP's ``time()`` function.
	::

		echo now('Australia/Victoria');

	If a timezone is not provided, it will return ``time()`` based on the
	**time_reference** setting.

.. php:function:: timezone_select([$class = '', $default = '', $what = \DateTimeZone::ALL, $country = null])

	:param	string	$class: Optional class to apply to the select field
	:param	string	$default: Default value for initial selection
	:param	int	$what: DateTimeZone class constants (see `listIdentifiers <https://www.php.net/manual/en/datetimezone.listidentifiers.php>`_)
	:param	string	$country: A two-letter ISO 3166-1 compatible country code (see `listIdentifiers <https://www.php.net/manual/en/datetimezone.listidentifiers.php>`_)
	:returns:	Preformatted HTML select field
	:rtype:	string

	Generates a `select` form field of available timezones (optionally filtered by `$what` and `$country`).
	You can supply an option class to apply to the field to make formatting easier, as well as a default
	selected value.
	::

		echo timezone_select('custom-select', 'America/New_York');

Many functions previously found in the CodeIgniter 3 ``date_helper`` have been moved to the ``I18n``
module in CodeIgniter 4.
