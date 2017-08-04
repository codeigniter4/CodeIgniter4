###############
Dates and Times
###############

CodeIgniter provides a fully-localized, immutable, date/time class that is built on PHP's DateTime object, but uses the Intl
extension's features to convert times across timezones and display the output correctly for different locales. This class
is the **Time** class and lives in the **CodeIgniter\I18n** namespace.

.. note:: Since the Time class extends DateTime, if there are features that you need that this class doesn't provide,
    you can likely find them within the DateTime class itself.

.. contents:: Page Contents
    :local:

=============
Instantiating
=============

There are several ways that a new Time instance can be created. The first is simply to create a new instance
like any other class. When you do it this way, you can pass in a string representing the desired time. This can
be any string that PHP's strtotime function can parse::

    use CodeIgniter\I18n\Time;

    $myTime = new Time('+3 week');
    $myTime = new Time('now');

You can pass in strings representing the timezone and the locale in the second and parameters, respectively. Timezones
can be any supported by PHP's `DateTimeZone <http://php.net/manual/en/timezones.php>`_ class. The locale can be
any supported by PHP's `Locale <http://php.net/manual/en/class.locale.php>`_ class. If no locale or timezone is
provided, the application defaults will be used.

::

    $myTime = new Time('now', 'America/Chicago', 'en_US');


now()
-----

The Time class has several helper methods to instantiate the class. The first of these is the **now()** method
that returns a new instance set to the current time. You can pass in strings representing the timezone and the locale
in the second and parameters, respectively. If no locale or timezone is provided, the application defaults will be used.

::

    $myTime = Time::now('America/Chicago', 'en_US');

parse()
-------

This helper method is a static version of the default constructor. It takes a string acceptable as DateTime's
constructor as the first parameter, a timezone as the second parameter, and the locale as the third parameter.::

    $myTime = Time::parse('next Tuesday', 'America/Chicago', 'en_US');

today()
-------

Returns a new instance with the date set to the current date, and the time set to midnight. It accepts strings
for the timezone and locale in the second and third parameters::

    $myTime = Time::today('America/Chicago', 'en_US');

yesterday()
-----------

Returns a new instance with the date set to the yesterday's date and the time set to midnight. It accepts strings
for the timezone and locale in the second and third parameters::

    $myTime = Time::yesterday('America/Chicago', 'en_US');

tomorrow()
-----------

Returns a new instance with the date set to the tomorrow's date and the time set to midnight. It accepts strings
for the timezone and locale in the second and third parameters::

    $myTime = Time::tomorrow('America/Chicago', 'en_US');

createFromDate()
----------------

Given separate inputs for **year**, **month**, and **day**, will return a new instance. If any of these parameters
are not provided, it will use the current value to fill it in. Accepts strings for the timezone and locale in the
fourth and fifth parameters::

    $today = Time::createFromDate();            // Uses current year, month, and day
    $anniversary = Time::createFromDate(2018);  // Uses current month and day
    $date = Time::createFromDate(2018, 3, 15, 'America/Chicago', 'en_US');

createFromTime()
----------------

Like **createFromDate** except it is only concerned with the **hours**, **minutes**, and **seconds**. Uses the
current day for the date portion of the Time instance. Accepts strings for the timezone and locale in the
fourth and fifth parameters::

    $lunch = Time::createFromTime(11, 30)       // 11:30 am today
    $dinner = Time::createFromTime(18, 00, 00)  // 6:00 pm today
    $time = Time::createFromTime($hour, $minutes, $seconds, $timezone, $locale);

create()
--------

A combination of the previous two methods, takes **year**, **month**, **day**, **hour**, **minutes**, and **seconds**
as separate parameters. Any value not provided will use the current date and time to determine. Accepts strings for the
timezone and locale in the fourth and fifth parameters::

    $time = Time::create($year, $month, $day, $hour, $minutes, $seconds, $timezone, $locale);

createFromFormat()
------------------

This is a replacement for DateTime's method of the same name. This allows the timezone to be set at the same time,
and returns a **Time** instance, instead of DateTime::

    $time = Time::createFromFormat('j-M-Y', '15-Feb-2009', 'America/Chicago');

createFromTimestamp()
---------------------

This method takes a UNIX timestamp and, optionally, the timezone and locale, to create a new Time instance::

    $time = Time::createFromTimestamp(1501821586, 'America/Chicago', 'en_US');

instance()
----------

When working with other libraries that provide a DateTime instance, you can use this method to convert that
to a Time instance, optionally setting the locale. The timezone will be automatically determined from the DateTime
instance passed in::

    $dt = new DateTime('now');
    $time = Time::instance($dt, 'en_US');

toDateTime()
------------

While not an instantiator, this method is the opposite of the **instance** method, allowing you to convert a Time
instance into a DateTime instance. This preserves the timezone setting, but loses the locale, since DateTime is
not aware of locales::

    $datetime = Time::toDateTime();

