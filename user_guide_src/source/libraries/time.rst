###############
Times and Dates
###############

CodeIgniter provides a fully-localized, immutable, date/time class that is built on PHP's DateTime object, but uses the Intl
extension's features to convert times across timezones and display the output correctly for different locales. This class
is the ``Time`` class and lives in the ``CodeIgniter\I18n`` namespace.

.. note:: Since the Time class extends DateTime, if there are features that you need that this class doesn't provide,
    you can likely find them within the DateTime class itself.

.. contents::
    :local:
    :depth: 2

=============
Instantiating
=============

There are several ways that a new Time instance can be created. The first is simply to create a new instance
like any other class. When you do it this way, you can pass in a string representing the desired time. This can
be any string that PHP's strtotime function can parse:

.. literalinclude:: time/001.php
   :lines: 2-

You can pass in strings representing the timezone and the locale in the second and parameters, respectively. Timezones
can be any supported by PHP's `DateTimeZone <https://www.php.net/manual/en/timezones.php>`__ class. The locale can be
any supported by PHP's `Locale <https://www.php.net/manual/en/class.locale.php>`__ class. If no locale or timezone is
provided, the application defaults will be used.

.. literalinclude:: time/002.php
   :lines: 2-

now()
-----

The Time class has several helper methods to instantiate the class. The first of these is the ``now()`` method
that returns a new instance set to the current time. You can pass in strings representing the timezone and the locale
in the second and parameters, respectively. If no locale or timezone is provided, the application defaults will be used.

.. literalinclude:: time/003.php
   :lines: 2-

parse()
-------

This helper method is a static version of the default constructor. It takes a string acceptable as DateTime's
constructor as the first parameter, a timezone as the second parameter, and the locale as the third parameter:

.. literalinclude:: time/004.php
   :lines: 2-

today()
-------

Returns a new instance with the date set to the current date, and the time set to midnight. It accepts strings
for the timezone and locale in the first and second parameters:

.. literalinclude:: time/005.php
   :lines: 2-

yesterday()
-----------

Returns a new instance with the date set to the yesterday's date and the time set to midnight. It accepts strings
for the timezone and locale in the first and second parameters:

.. literalinclude:: time/006.php
   :lines: 2-

tomorrow()
-----------

Returns a new instance with the date set to tomorrow's date and the time set to midnight. It accepts strings
for the timezone and locale in the first and second parameters:

.. literalinclude:: time/007.php
   :lines: 2-

createFromDate()
----------------

Given separate inputs for **year**, **month**, and **day**, will return a new instance. If any of these parameters
are not provided, it will use the current value to fill it in. Accepts strings for the timezone and locale in the
fourth and fifth parameters:

.. literalinclude:: time/008.php
   :lines: 2-

createFromTime()
----------------

Like ``createFromDate()`` except it is only concerned with the **hours**, **minutes**, and **seconds**. Uses the
current day for the date portion of the Time instance. Accepts strings for the timezone and locale in the
fourth and fifth parameters:

.. literalinclude:: time/009.php
   :lines: 2-

create()
--------

A combination of the previous two methods, takes **year**, **month**, **day**, **hour**, **minutes**, and **seconds**
as separate parameters. Any value not provided will use the current date and time to determine. Accepts strings for the
timezone and locale in the fourth and fifth parameters:

.. literalinclude:: time/010.php
   :lines: 2-

createFromFormat()
------------------

This is a replacement for DateTime's method of the same name. This allows the timezone to be set at the same time,
and returns a ``Time`` instance, instead of DateTime:

.. literalinclude:: time/011.php
   :lines: 2-

createFromTimestamp()
---------------------

This method takes a UNIX timestamp and, optionally, the timezone and locale, to create a new Time instance:

.. literalinclude:: time/012.php
   :lines: 2-

createFromInstance()
--------------------

When working with other libraries that provide a DateTime instance, you can use this method to convert that
to a Time instance, optionally setting the locale. The timezone will be automatically determined from the DateTime
instance passed in:

.. literalinclude:: time/013.php
   :lines: 2-

toDateTime()
------------

While not an instantiator, this method is the opposite of the **instance** method, allowing you to convert a Time
instance into a DateTime instance. This preserves the timezone setting, but loses the locale, since DateTime is
not aware of locales:

.. literalinclude:: time/014.php
   :lines: 2-

====================
Displaying the Value
====================

Since the Time class extends DateTime, you get all of the output methods that provides, including the format() method.
However, the DateTime methods do not provide a localized result. The Time class does provide a number of helper methods
to display localized versions of the value, though.

toLocalizedString()
-------------------

This is the localized version of DateTime's ``format()`` method. Instead of using the values you might be familiar with, though,
you must use values acceptable to the `IntlDateFormatter <https://www.php.net/manual/en/class.intldateformatter.php>`__ class.
A full listing of values can be found `here <https://unicode-org.github.io/icu-docs/apidoc/released/icu4c/classSimpleDateFormat.html#details>`__.

.. literalinclude:: time/015.php
   :lines: 2-

toDateTimeString()
------------------

This is the first of three helper methods to work with the IntlDateFormatter without having to remember their values.
This will return a string formatted as you would commonly use for datetime columns in a database (Y-m-d H:i:s):

.. literalinclude:: time/016.php
   :lines: 2-

toDateString()
--------------

Displays just the date portion of the Time:

.. literalinclude:: time/017.php
   :lines: 2-

toTimeString()
--------------

Displays just the time portion of the value:

.. literalinclude:: time/018.php
   :lines: 2-

humanize()
----------

This methods returns a string that displays the difference between the current date/time and the instance in a
human readable format that is geared towards being easily understood. It can create strings like '3 hours ago',
'in 1 month', etc:

.. literalinclude:: time/019.php
   :lines: 2-

The exact time displayed is determined in the following manner:

=============================== =================================
Time difference                  Result
=============================== =================================
$time > 1 year && < 2 years      in 1 year / 1 year ago
$time > 1 month && < 1 year      in 6 months / 6 months ago
$time > 7 days && < 1 month      in 3 weeks / 3 weeks ago
$time > today && < 7 days        in 4 days / 4 days ago
$time == tomorrow / yesterday    Tomorrow / Yesterday
$time > 59 minutes && < 1 day    in 2 hours / 2 hours ago
$time > now && < 1 hour          in 35 minutes / 35 minutes ago
$time == now                     Now
=============================== =================================

The exact language used is controlled through the language file, **Time.php**.

==============================
Working with Individual Values
==============================

The Time object provides a number of methods to allow to get and set individual items, like the year, month, hour, etc,
of an existing instance. All of the values retrieved through the following methods will be fully localized and respect
the locale that the Time instance was created with.

All of the following ``getX()`` and ``setX()`` methods can also be used as if they were a class property. So, any calls to methods
like ``getYear()`` can also be accessed through ``$time->year``, and so on.

Getters
-------

The following basic getters exist:

.. literalinclude:: time/020.php
   :lines: 2-

In addition to these, a number of methods exist to provide additional information about the date:

.. literalinclude:: time/021.php
   :lines: 2-

getAge()
--------

Returns the age, in years, of between the Time's instance and the current time. Perfect for checking
the age of someone based on their birthday:

.. literalinclude:: time/022.php
   :lines: 2-

getDST()
--------

Returns boolean true/false based on whether the Time instance is currently observing Daylight Savings Time:

.. literalinclude:: time/023.php
   :lines: 2-

getLocal()
----------

Returns boolean true if the Time instance is in the same timezone as the application is currently running in:

.. literalinclude:: time/024.php
   :lines: 2-

getUtc()
--------

Returns boolean true if the Time instance is in UTC time:

.. literalinclude:: time/025.php
   :lines: 2-

getTimezone()
-------------

Returns a new `DateTimeZone <https://www.php.net/manual/en/class.datetimezone.php>`__ object set the timezone of the Time
instance:

.. literalinclude:: time/026.php
   :lines: 2-

getTimezoneName()
-----------------

Returns the full `timezone string <https://www.php.net/manual/en/timezones.php>`__ of the Time instance:

.. literalinclude:: time/027.php
   :lines: 2-

Setters
=======

The following basic setters exist. If any of the values set are out of range, an ``InvalidArgumentExeption`` will be
thrown.

.. note:: All setters will return a new Time instance, leaving the original instance untouched.

.. note:: All setters will throw an InvalidArgumentException if the value is out of range.

.. literalinclude:: time/028.php
   :lines: 2-

setTimezone()
-------------

Converts the time from it's current timezone into the new one:

.. literalinclude:: time/029.php
   :lines: 2-

setTimestamp()
--------------

Returns a new instance with the date set to the new timestamp:

.. literalinclude:: time/030.php
   :lines: 2-

Modifying the Value
===================

The following methods allow you to modify the date by adding or subtracting values to the current Time. This will not
modify the existing Time instance, but will return a new instance.

.. literalinclude:: time/031.php
   :lines: 2-

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
   :lines: 2-

The value being tested against can be a Time instance, a DateTime instance, or a string with the full date time in
a manner that a new DateTime instance can understand. When passing a string as the first parameter, you can pass
a timezone string in as the second parameter. If no timezone is given, the system default will be used:

.. literalinclude:: time/033.php
   :lines: 2-

sameAs()
--------

This is identical to the ``equals()`` method, except that it only returns true when the date, time, AND timezone are
all identical:

.. literalinclude:: time/034.php
   :lines: 2-

isBefore()
----------

Checks if the passed in time is before the current instance. The comparison is done against the UTC versions of
both times:

.. literalinclude:: time/035.php
   :lines: 2-

The value being tested against can be a Time instance, a DateTime instance, or a string with the full date time in
a manner that a new DateTime instance can understand. When passing a string as the first parameter, you can pass
a timezone string in as the second parameter. If no timezone is given, the system default will be used:

.. literalinclude:: time/036.php
   :lines: 2-

isAfter()
---------

Works exactly the same as ``isBefore()`` except checks if the time is after the time passed in:

.. literalinclude:: time/037.php
   :lines: 2-

Viewing Differences
===================

To compare two Times directly, you would use the ``difference()`` method, which returns a ``CodeIgniter\I18n\TimeDifference``
instance. The first parameter is either a Time instance, a DateTime instance, or a string with the date/time. If
a string is passed in the first parameter, the second parameter can be a timezone string:

.. literalinclude:: time/038.php
   :lines: 2-

Once you have the TimeDifference instance, you have several methods you can use to find information about the difference
between the two times. The value returned will be negative if it was in the past, or positive if in the future from
the original time:

.. literalinclude:: time/039.php
   :lines: 2-

You can use either ``getX()`` methods, or access the calculate values as if they were properties:

.. literalinclude:: time/040.php
   :lines: 2-

humanize()
----------

Much like Time's ``humanize()`` method, this returns a string that displays the difference between the times in a
human readable format that is geared towards being easily understood. It can create strings like '3 hours ago',
'in 1 month', etc. The biggest differences are in how very recent dates are handled:

.. literalinclude:: time/041.php
   :lines: 2-

The exact time displayed is determined in the following manner:

=============================== =================================
Time difference                  Result
=============================== =================================
$time > 1 year && < 2 years      in 1 year / 1 year ago
$time > 1 month && < 1 year      in 6 months / 6 months ago
$time > 7 days && < 1 month      in 3 weeks / 3 weeks ago
$time > today && < 7 days        in 4 days / 4 days ago
$time > 1 hour && < 1 day        in 8 hours / 8 hours ago
$time > 1 minute && < 1 hour     in 35 minutes / 35 minutes ago
$time < 1 minute                 Now
=============================== =================================

The exact language used is controlled through the language file, **Time.php**.
