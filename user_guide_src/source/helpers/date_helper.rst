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

Many functions previously found in the CodeIgniter 3 ``date_helper`` have been moved to the ``I18n``
module in CodeIgniter 4.
