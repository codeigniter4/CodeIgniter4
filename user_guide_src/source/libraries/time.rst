###############
Times and Dates
###############

CodeIgniter provides a fully-localized, immutable, date/time class that is built on PHP's DateTimeImmutable class, but uses the Intl
extension's features to convert times across timezones and display the output correctly for different locales. This class
is the ``Time`` class and lives in the ``CodeIgniter\I18n`` namespace.

.. note:: Since the Time class extends ``DateTimeImmutable``, if there are features that you need that this class doesn't provide,
    you can likely find them within the `DateTimeImmutable`_  class itself.

.. _DateTimeImmutable: https://www.php.net/manual/en/class.datetimeimmutable.php

.. note:: Prior to v4.3.0, the Time class extended ``DateTime`` and some inherited methods changed
    the current object state. The bug was fixed in v4.3.0. If you need the old Time class for backward
    compatibility, you can use deprecated ``TimeLegacy`` class for the time being.

.. contents::
    :local:
    :depth: 2

*************
Instantiating
*************

There are several ways that a new Time instance can be created. The first is simply to create a new instance
like any other class.

When you do it this way, you can pass in a string representing the desired time.
This can be any string that PHP's `DateTimeImmutable`_ constructor can parse. See
`Supported Date and Time Formats`_ for details.

.. _Supported Date and Time Formats: https://www.php.net/manual/en/datetime.formats.php

.. literalinclude:: time/001.php

You can pass in strings representing the timezone and the locale in the second and the third parameters, respectively. The timezone
can be any one supported by PHP's `DateTimeZone <https://www.php.net/manual/en/timezones.php>`__ class. The locale can be
any one supported by PHP's `Locale <https://www.php.net/manual/en/class.locale.php>`__ class. If no locale or timezone is
provided, the application defaults will be used.

.. literalinclude:: time/002.php

now()
=====

The Time class has several helper methods to instantiate the class. The first of these is the ``now()`` method
that returns a new instance set to the current time. You can pass in strings representing the timezone and locale
in the second and third parameters, respectively. If no locale or timezone is provided, the application defaults will be used.

.. literalinclude:: time/003.php

parse()
=======

This helper method is a static version of the default constructor. It takes a string acceptable as DateTimeImmutable's
constructor as the first parameter, a timezone as the second parameter, and the locale as the third parameter:

.. literalinclude:: time/004.php

today()
=======

Returns a new instance with the date set to the current date, and the time set to midnight. It accepts strings
for the timezone and locale in the first and second parameters:

.. literalinclude:: time/005.php

yesterday()
===========

Returns a new instance with the date set to the yesterday's date and the time set to midnight. It accepts strings
for the timezone and locale in the first and second parameters:

.. literalinclude:: time/006.php

tomorrow()
==========

Returns a new instance with the date set to tomorrow's date and the time set to midnight. It accepts strings
for the timezone and locale in the first and second parameters:

.. literalinclude:: time/007.php

createFromDate()
================

Given separate inputs for **year**, **month**, and **day**, will return a new instance. If any of these parameters
are not provided, it will use the current year, month and day. Accepts strings for the timezone and locale in the
fourth and fifth parameters:

.. literalinclude:: time/008.php

createFromTime()
================

Like ``createFromDate()``, except it is only concerned with the **hours**, **minutes**, and **seconds**. Uses the
current day for the date portion of the Time instance. Accepts strings for the timezone and locale in the
fourth and fifth parameters:

.. literalinclude:: time/009.php

create()
========

A combination of the previous two methods, takes **year**, **month**, **day**, **hour**, **minutes**, and **seconds**
as separate parameters. Any value not provided will use the current date and time. Accepts strings for the
timezone and locale in the fourth and fifth parameters:

.. literalinclude:: time/010.php

createFromFormat()
==================

This is a replacement for DateTimeImmutable's method of the same name. This allows the timezone to be set at the same time,
and returns a ``Time`` instance, instead of DateTimeImmutable:

.. literalinclude:: time/011.php

.. _time-createfromtimestamp:

createFromTimestamp()
=====================

This method takes a UNIX timestamp and, optionally, the timezone and locale, to
create a new Time instance:

.. literalinclude:: time/012.php

If you do not explicitly pass a timezone, it returns a Time instance with **UTC**.

.. note:: We recommend to always call ``createFromTimestamp()`` with 2 parameters
    (i.e. explicitly pass a timezone) unless using UTC as the default timezone.

.. note:: In v4.4.6 to prior to v4.6.0, this method returned a Time instance
    with the default timezone when you do not specify a timezone.

createFromInstance()
====================

When working with other libraries that provide a DateTime instance, you can use this method to convert that
to a Time instance, optionally setting the locale. The timezone will be automatically determined from the DateTime
instance passed in:

.. literalinclude:: time/013.php

toDateTime()
============

While not an instantiator, this method is the opposite of the **instance** method, allowing you to convert a Time
instance into a DateTime instance. This preserves the timezone setting, but loses the locale, since DateTime is
not aware of locales:

.. literalinclude:: time/014.php


********************
Displaying the Value
********************

Since the Time class extends DateTimeImmutable, you get all of the output methods that provides, including the ``format()`` method.
However, the DateTimeImmutable methods do not provide a localized result. The Time class does provide a number of helper methods
to display localized versions of the value, though.

toLocalizedString()
===================

This is the localized version of DateTimeImmutable's ``format()`` method. Instead of using the values you might be familiar with, though,
you must use values acceptable to the `IntlDateFormatter <https://www.php.net/manual/en/class.intldateformatter.php>`__ class.
A full listing of values can be found `here <https://unicode-org.github.io/icu-docs/apidoc/released/icu4c/classSimpleDateFormat.html#details>`__.

.. literalinclude:: time/015.php

.. _time-todatetimestring:

toDateTimeString()
==================

This is the first of three helper methods to work with the `IntlDateFormatter <https://www.php.net/manual/en/class.intldateformatter.php>`_ without having to remember their values.
This will return a localized version of string formatted as (``Y-m-d H:i:s``):

.. literalinclude:: time/016.php

toDateString()
==============

Displays just the localized date portion of the Time:

.. literalinclude:: time/017.php

toTimeString()
==============

Displays just the localized time portion of the value:

.. literalinclude:: time/018.php

humanize()
==========

This method returns a string that displays the difference between the current date/time and the instance in a
human readable format that is geared towards being easily understood. It can create strings like '3 hours ago',
'in 1 month', etc:

.. literalinclude:: time/019.php

The exact time displayed is determined in the following manner:

=============================== =================================
Time difference                  Result
=============================== =================================
1 year < $time < 2 years         in 1 year / 1 year ago
1 month < $time < 1 year         in 6 months / 6 months ago
7 days < $time < 1 month         in 3 weeks / 3 weeks ago
today < $time < 7 days           in 4 days / 4 days ago
$time == yesterday / tomorrow    Yesterday / Tomorrow
59 minutes < $time < 1 day       in 2 hours / 2 hours ago
now < $time < 1 hour             in 35 minutes / 35 minutes ago
$time == now                     Now
=============================== =================================

The result strings are coming from the language file, **system/Language/en/Time.php**.
If you want to overwrite them, create **app/Language/{locale}/Time.php**.

******************************
Working with Individual Values
******************************

The Time object provides a number of methods to allow to get and set individual items, like the year, month, hour, etc,
of an existing instance. All of the values retrieved through the following methods will be fully localized and respect
the locale that the Time instance was created with.

All of the following ``getX()`` and ``setX()`` methods can also be used as if they were a class property. So, any calls to methods
like ``getYear()`` can also be accessed through ``$time->year``, and so on.

Getters
=======

The following basic getters exist:

.. literalinclude:: time/020.php

In addition to these, a number of methods exist to provide additional information about the date:

.. literalinclude:: time/021.php

getAge()
--------

Returns the age, in years, between the Time instance and the current time. Perfect for checking
the age of someone based on their birthday:

.. literalinclude:: time/022.php

getDST()
--------

Returns boolean true/false based on whether the Time instance is currently observing Daylight Savings Time:

.. literalinclude:: time/023.php

getLocal()
----------

Returns boolean true if the Time instance is in the same timezone as the application is currently running in:

.. literalinclude:: time/024.php

getUtc()
--------

Returns boolean true if the Time instance is in UTC time:

.. literalinclude:: time/025.php

getTimezone()
-------------

Returns a new `DateTimeZone <https://www.php.net/manual/en/class.datetimezone.php>`__ object set the timezone of the Time
instance:

.. literalinclude:: time/026.php

getTimezoneName()
-----------------

Returns the full `timezone string <https://www.php.net/manual/en/timezones.php>`__ of the Time instance:

.. literalinclude:: time/027.php

Setters
=======

The following basic setters exist. If any of the values set are out of range, an ``InvalidArgumentExeption`` will be
thrown.

.. note:: All setters will return a new Time instance, leaving the original instance untouched.

.. note:: All setters will throw an InvalidArgumentException if the value is out of range.

.. literalinclude:: time/028.php

setTimezone()
-------------

Converts the time from it's current timezone into the new one:

.. literalinclude:: time/029.php

setTimestamp()
--------------

Returns a new instance with the date set to the new timestamp:

.. literalinclude:: time/030.php

.. note:: Prior to v4.6.0, due to a bug, this method might return incorrect
    date/time. See :ref:`Upgrading Guide <upgrade-460-time-set-timestamp>` for details.

Modifying the Value
===================

The following methods allow you to modify the date by adding or subtracting values to the current Time. This will not
modify the existing Time instance, but will return a new instance.

.. literalinclude:: time/031.php

addCalendarMonths() / subCalendarMonths()
-----------------------------------------

Modifies the current Time by adding or subtracting whole calendar months. These methods can be useful if you 
require no calendar months are skipped in recurring dates. Refer to the table below for a comparison between 
``addMonths()`` and ``addCalendarMonths()`` for an initial date of ``2025-01-31``.

======= =========== =================== 
$months addMonths() addCalendarMonths()
======= =========== ===================
1       2025-03-03  2025-02-28          
2       2025-03-31  2025-03-31          
3       2025-05-01  2025-04-30       
4       2025-05-31  2025-05-31
5       2025-07-01  2025-06-30
6       2025-07-31  2025-07-31
======= =========== ===================

Comparing Two Times
===================

The following methods allow you to compare one Time instance with another. All comparisons are first converted to UTC
before comparisons are done, to ensure that different timezones respond correctly.

equals()
--------

Determines if the datetime passed in is equal to the current instance. Equal in this case means that they represent the
same moment in time, and are not required to be in the same timezone, as both times are converted to UTC and compared
that way:

.. literalinclude:: time/032.php

The value being tested against can be a Time instance, a DateTime instance, or a string with the full date time in
a manner that a new DateTime instance can understand. When passing a string as the first parameter, you can pass
a timezone string in as the second parameter. If no timezone is given, the system default will be used:

.. literalinclude:: time/033.php

sameAs()
--------

This is identical to the ``equals()`` method, except that it only returns true when the date, time, AND timezone are
all identical:

.. literalinclude:: time/034.php

isBefore()
----------

Checks if the passed in time is before the current instance. The comparison is done against the UTC versions of
both times:

.. literalinclude:: time/035.php

The value being tested against can be a Time instance, a DateTime instance, or a string with the full date time in
a manner that a new DateTime instance can understand. When passing a string as the first parameter, you can pass
a timezone string in as the second parameter. If no timezone is given, the system default will be used:

.. literalinclude:: time/036.php

isAfter()
---------

Works exactly the same as ``isBefore()`` except checks if the time is after the time passed in:

.. literalinclude:: time/037.php

.. _time-viewing-differences:

Viewing Differences
===================

To compare two Times directly, you would use the ``difference()`` method, which returns a ``CodeIgniter\I18n\TimeDifference``
instance.

The first parameter is either a Time instance, a DateTime instance, or a string with the date/time. If
a string is passed in the first parameter, the second parameter can be a timezone string:

.. literalinclude:: time/038.php

Once you have the TimeDifference instance, you have several methods you can use to find information about the difference
between the two times. The value returned will be negative if it was in the past, or positive if in the future from
the original time:

.. literalinclude:: time/039.php

.. note:: Prior to v4.4.7, Time always converted the time zones to UTC before
    comparison. This could lead to unexpected results when containing a day
    differed from 24 hours due to Daylight Saving Time (DST).

    Starting with v4.4.7, when comparing date/times that are in the same
    time zone, the comparison is performed as is, without conversion to UTC.

        .. literalinclude:: time/042.php

You can use either ``getX()`` methods, or access the calculate values as if they were properties:

.. literalinclude:: time/040.php

humanize()
----------

Much like Time's ``humanize()`` method, this returns a string that displays the difference between the times in a
human readable format that is geared towards being easily understood. It can create strings like '3 hours ago',
'in 1 month', etc. The biggest difference is in how very recent dates are handled:

.. literalinclude:: time/041.php

The exact time displayed is determined in the following manner:

=============================== =================================
Time difference                  Result
=============================== =================================
1 year < $time < 2 years         in 1 year / 1 year ago
1 month < $time < 1 year         in 6 months / 6 months ago
7 days < $time < 1 month         in 3 weeks / 3 weeks ago
today < $time < 7 days           in 4 days / 4 days ago
1 hour < $time < 1 day           in 8 hours / 8 hours ago
1 minute < $time < 1 hour        in 35 minutes / 35 minutes ago
$time < 1 minute                 Now
=============================== =================================

The result strings are coming from the language file, **system/Language/en/Time.php**.
If you want to overwrite them, create **app/Language/{locale}/Time.php**.
