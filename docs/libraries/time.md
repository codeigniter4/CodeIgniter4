# Times and Dates

CodeIgniter provides a fully localized, immutable date/time class that is built on PHP's DateTimeImmutable class, but uses the Intl extension's features to convert times across timezones and display the output correctly for different locales. This class is the `Time` class and lives in the `CodeIgniter\I18n` namespace.

!!! note "Note"
    Since the Time class extends `DateTimeImmutable`, if there are features that you need that this class doesn't provide, you can likely find them within the [DateTimeImmutable](https://www.php.net/manual/en/class.datetimeimmutable.php) class itself.

!!! note "Note"
    Prior to v4.3.0, the Time class extended `DateTime` and some inherited methods changed the current object state. The bug was fixed in v4.3.0. If you need the old Time class for backward compatibility, you can use the deprecated `TimeLegacy` class for the time being.

## Instantiating

There are several ways that a new Time instance can be created. The first is simply to create a new instance like any other class.

When you do it this way, you can pass in a string representing the desired time. This can be any string that PHP's [DateTimeImmutable](https://www.php.net/manual/en/class.datetimeimmutable.php) constructor can parse. See [Supported Date and Time Formats](https://www.php.net/manual/en/datetime.formats.php) for details.

```php
<?php

use CodeIgniter\I18n\Time;

$myTime = new Time('2024-01-01');
$myTime = new Time('2024-01-01 12:00:00');
$myTime = new Time('now');
$myTime = new Time('+3 week');
```

You can pass in strings representing the timezone and the locale in the second and the third parameters, respectively. The timezone can be any one supported by PHP's [DateTimeZone](https://www.php.net/manual/en/timezones.php) class. The locale can be any one supported by PHP's [Locale](https://www.php.net/manual/en/class.locale.php) class. If no locale or timezone is provided, the application defaults will be used.

```php
<?php

use CodeIgniter\I18n\Time;

$myTime = new Time('now', 'America/Chicago', 'en_US');
```

### now()

The Time class has several helper methods to instantiate the class. The first of these is the `now()` method that returns a new instance set to the current time. You can pass in strings representing the timezone and locale in the first and second parameters, respectively. If no locale or timezone is provided, the application defaults will be used.

```php
<?php

use CodeIgniter\I18n\Time;

$myTime = Time::now('America/Chicago', 'en_US');
```

### parse()

This helper method is a static version of the default constructor. It takes a string acceptable as DateTimeImmutable's constructor as the first parameter, a timezone as the second parameter, and the locale as the third parameter:

```php
<?php

use CodeIgniter\I18n\Time;

$myTime = Time::parse('next Tuesday', 'America/Chicago', 'en_US');
```

### today()

Returns a new instance with the date set to the current date, and the time set to midnight. It accepts strings for the timezone and locale in the first and second parameters:

```php
<?php

use CodeIgniter\I18n\Time;

$myTime = Time::today('America/Chicago', 'en_US');
```

### yesterday()

Returns a new instance with the date set to yesterday's date and the time set to midnight. It accepts strings for the timezone and locale in the first and second parameters:

```php
<?php

use CodeIgniter\I18n\Time;

$myTime = Time::yesterday('America/Chicago', 'en_US');
```

### tomorrow()

Returns a new instance with the date set to tomorrow's date and the time set to midnight. It accepts strings for the timezone and locale in the first and second parameters:

```php
<?php

use CodeIgniter\I18n\Time;

$myTime = Time::tomorrow('America/Chicago', 'en_US');
```

### createFromDate()

Given separate inputs for **year**, **month**, and **day**, it will return a new instance. If any of these parameters are not provided, it will use the current year, month and day. Accepts strings for the timezone and locale in the fourth and fifth parameters:

```php
<?php

use CodeIgniter\I18n\Time;

$today       = Time::createFromDate();     // Uses current year, month, and day
$anniversary = Time::createFromDate(2018); // Uses current month and day
$date        = Time::createFromDate(2018, 3, 15, 'America/Chicago', 'en_US');
```

### createFromTime()

Like `createFromDate()`, except it is only concerned with the **hours**, **minutes**, and **seconds**. Uses the current day for the date portion of the Time instance. Accepts strings for the timezone and locale in the fourth and fifth parameters:

```php
<?php

use CodeIgniter\I18n\Time;

$lunch  = Time::createFromTime(11, 30);     // 11:30 am today
$dinner = Time::createFromTime(18, 00, 00); // 6:00 pm today
$time   = Time::createFromTime($hour, $minutes, $seconds, $timezone, $locale);
```

### create()

A combination of the previous two methods, this takes **year**, **month**, **day**, **hour**, **minutes**, and **seconds** as separate parameters. Any value not provided will use the current date and time. Accepts strings for the timezone and locale in the seventh and eighth parameters:

```php
<?php

use CodeIgniter\I18n\Time;

$time = Time::create($year, $month, $day, $hour, $minutes, $seconds, $timezone, $locale);
```

### createFromFormat()

This is a replacement for DateTimeImmutable's method of the same name. This allows the timezone to be set at the same time, and returns a `Time` instance, instead of DateTimeImmutable:

```php
<?php

use CodeIgniter\I18n\Time;

$time = Time::createFromFormat('j-M-Y', '15-Feb-2009', 'America/Chicago');
```

### createFromTimestamp()

This method takes a UNIX timestamp and, optionally, the timezone and locale, to create a new Time instance:

```php
<?php

use CodeIgniter\I18n\Time;

$time = Time::createFromTimestamp(1501821586, 'America/Chicago', 'en_US');
```

If you do not explicitly pass a timezone, it returns a Time instance with **UTC**.

!!! note "Note"
    We recommend always calling `createFromTimestamp()` with 2 parameters (i.e. explicitly pass a timezone) unless using UTC as the default timezone.

!!! note "Note"
    From v4.4.6 up to, but not including, v4.6.0, this method returned a Time instance with the default timezone when no timezone was specified.

### createFromInstance()

When working with other libraries that provide a DateTime instance, you can use this method to convert that to a Time instance, optionally setting the locale. The timezone will be automatically determined from the DateTime instance passed in:

```php
<?php

use CodeIgniter\I18n\Time;

$dt   = new \DateTime('now');
$time = Time::createFromInstance($dt, 'en_US');
```

### toDateTime()

While not an instantiator, this method is the opposite of the `createFromInstance()` method, allowing you to convert a Time instance into a DateTime instance. This preserves the timezone setting, but loses the locale, since DateTime is not aware of locales:

```php
<?php

use CodeIgniter\I18n\Time;

$datetime = Time::toDateTime();
```

## Displaying the Value

Since the Time class extends DateTimeImmutable, you get all of the output methods that it provides, including the `format()` method. However, the DateTimeImmutable methods do not provide a localized result. The Time class does provide a number of helper methods to display localized versions of the value, though.

### toLocalizedString()

This is the localized version of DateTimeImmutable's `format()` method. Instead of using the values you might be familiar with, though, you must use values acceptable to the [IntlDateFormatter](https://www.php.net/manual/en/class.intldateformatter.php) class. A full listing of values can be found [here](https://unicode-org.github.io/icu-docs/apidoc/released/icu4c/classSimpleDateFormat.html#details).

```php
<?php

use CodeIgniter\I18n\Time;

// Locale: en
$time = Time::parse('March 9, 2016 12:00:00', 'America/Chicago');
echo $time->toLocalizedString('MMM d, yyyy'); // March 9, 2016

// Locale: fa
$time = Time::parse('March 9, 2016 12:00:00', 'America/Chicago');
echo $time->toLocalizedString('MMM d, yyyy'); // مارس ۹, ۲۰۱۶
```

### toDateTimeString()

This is the first of three helper methods to work with the [IntlDateFormatter](https://www.php.net/manual/en/class.intldateformatter.php) without having to remember their values. This will return a localized version of a string formatted as `Y-m-d H:i:s`:

```php
<?php

use CodeIgniter\I18n\Time;

// Locale: en
$time = Time::parse('March 9, 2016 12:00:00', 'America/Chicago');
echo $time->toDateTimeString(); // 2016-03-09 12:00:00

// Locale: fa
$time = Time::parse('March 9, 2016 12:00:00', 'America/Chicago');
echo $time->toDateTimeString(); // ۲۰۱۶-۰۳-۰۹ ۱۲:۰۰:۰۰
```

### toDateString()

Displays just the localized date portion of the Time:

```php
<?php

use CodeIgniter\I18n\Time;

// Locale: en
$time = Time::parse('March 9, 2016 12:00:00', 'America/Chicago');
echo $time->toDateString(); // 2016-03-09

// Locale: fa
$time = Time::parse('March 9, 2016 12:00:00', 'America/Chicago');
echo $time->toDateString(); // ۲۰۱۶-۰۳-۰۹
```

### toTimeString()

Displays just the localized time portion of the value:

```php
<?php

use CodeIgniter\I18n\Time;

// Locale: en
$time = Time::parse('March 9, 2016 12:00:00', 'America/Chicago');
echo $time->toTimeString(); // 12:00:00

// Locale: fa
$time = Time::parse('March 9, 2016 12:00:00', 'America/Chicago');
echo $time->toTimeString(); // ۱۲:۰۰:۰۰
```

### humanize()

This method returns a string that displays the difference between the current date/time and the instance in a human-readable format that is geared towards being easily understood. It can create strings like '3 hours ago', 'in 1 month', etc.:

```php
<?php

use CodeIgniter\I18n\Time;

// Assume current time is: March 10, 2017 (America/Chicago)
$time = Time::parse('March 9, 2016 12:00:00', 'America/Chicago');

echo $time->humanize(); // 1 year ago
```

The exact time displayed is determined in the following manner:

| Time difference               | Result                         |
|-------------------------------|--------------------------------|
| 1 year < $time < 2 years    | in 1 year / 1 year ago         |
| 1 month < $time < 1 year    | in 6 months / 6 months ago     |
| 7 days < $time < 1 month    | in 3 weeks / 3 weeks ago       |
| today < $time < 7 days      | in 4 days / 4 days ago         |
| $time == yesterday / tomorrow | Yesterday / Tomorrow           |
| 59 minutes < $time < 1 day  | in 2 hours / 2 hours ago       |
| now < $time < 1 hour        | in 35 minutes / 35 minutes ago |
| $time == now                  | Now                            |

The result strings come from the language file, **system/Language/en/Time.php**. If you want to overwrite them, create **app/Language/{locale}/Time.php**.

## Working with Individual Values

The Time object provides a number of methods that allow you to get and set individual items, like the year, month, hour, etc., of an existing instance. All of the values retrieved through the following methods will be fully localized and respect the locale that the Time instance was created with.

All of the following `getX()` and `setX()` methods can also be used as if they were class properties. So, methods like `getYear()` can also be accessed through `$time->year`, and so on.

### Getters

The following basic getters exist:

```php
<?php

use CodeIgniter\I18n\Time;

$time = Time::parse('August 12, 2016 4:15:23pm');

// The output may vary based on locale.
echo $time->getYear();   // '2016'
echo $time->getMonth();  // '8'
echo $time->getDay();    // '12'
echo $time->getHour();   // '16'
echo $time->getMinute(); // '15'
echo $time->getSecond(); // '23'

echo $time->year;   // '2016'
echo $time->month;  // '8'
echo $time->day;    // '12'
echo $time->hour;   // '16'
echo $time->minute; // '15'
echo $time->second; // '23'
```

In addition to these, a number of methods exist to provide additional information about the date:

```php
<?php

use CodeIgniter\I18n\Time;

$time = Time::parse('August 12, 2016 4:15:23pm');

// The output may vary based on locale.
echo $time->getDayOfWeek();   // '6'
echo $time->getDayOfYear();   // '225'
echo $time->getWeekOfMonth(); // '2'
echo $time->getWeekOfYear();  // '33'
echo $time->getTimestamp();   // 1471018523 - UNIX timestamp (locale independent)
echo $time->getQuarter();     // '3'

echo $time->dayOfWeek;   // '6'
echo $time->dayOfYear;   // '225'
echo $time->weekOfMonth; // '2'
echo $time->weekOfYear;  // '33'
echo $time->timestamp;   // 1471018523
echo $time->quarter;     // '3'
```

#### getAge()

Returns the age, in years, between the Time instance and the current time. Perfect for checking the age of someone based on their birthday:

```php
<?php

use CodeIgniter\I18n\Time;

$time = Time::parse('5 years ago');

echo $time->getAge(); // 5
echo $time->age;      // 5
```

#### getDST()

Returns boolean true/false based on whether the Time instance is currently observing Daylight Saving Time:

```php
<?php

use CodeIgniter\I18n\Time;

echo Time::createFromDate(2012, 1, 1)->getDst(); // false
echo Time::createFromDate(2012, 9, 1)->dst;      // true
```

#### getLocal()

Returns boolean true if the Time instance is in the same timezone as the application is currently running in:

```php
<?php

use CodeIgniter\I18n\Time;

echo Time::now()->getLocal();           // true
echo Time::now('Europe/London')->local; // false
```

#### getUtc()

Returns boolean true if the Time instance is in UTC time:

```php
<?php

use CodeIgniter\I18n\Time;

echo Time::now('America/Chicago')->getUtc(); // false
echo Time::now('UTC')->utc;                  // true
```

#### getTimezone()

Returns a new [DateTimeZone](https://www.php.net/manual/en/class.datetimezone.php) object set to the timezone of the Time instance:

```php
<?php

use CodeIgniter\I18n\Time;

$tz = Time::now()->getTimezone();
$tz = Time::now()->timezone;

echo $tz->getName();
echo $tz->getOffset();
```

#### getTimezoneName()

Returns the full [timezone string](https://www.php.net/manual/en/timezones.php) of the Time instance:

```php
<?php

use CodeIgniter\I18n\Time;

echo Time::now('America/Chicago')->getTimezoneName(); // America/Chicago
echo Time::now('Europe/London')->timezoneName;        // Europe/London
```

### Setters

The following basic setters exist. If any of the values set are out of range, an `InvalidArgumentException` will be thrown.

!!! note "Note"
    All setters will return a new Time instance, leaving the original instance untouched.

!!! note "Note"
    All setters will throw an InvalidArgumentException if the value is out of range.

```php
<?php

$time = $time->setYear(2017);
$time = $time->setMonth(4);       // April
$time = $time->setMonth('April');
$time = $time->setMonth('Feb');   // February
$time = $time->setDay(25);
$time = $time->setHour(14);       // 2:00 pm
$time = $time->setMinute(30);
$time = $time->setSecond(54);
```

#### setTimezone()

Converts the time from its current timezone into the new one:

```php
<?php

use CodeIgniter\I18n\Time;

$time  = Time::parse('13 May 2020 10:00', 'America/Chicago');
$time2 = $time->setTimezone('Europe/London'); // Returns new instance converted to new timezone

echo $time->getTimezoneName();  // American/Chicago
echo $time2->getTimezoneName(); // Europe/London

echo $time->toDateTimeString();  // 2020-05-13 10:00:00
echo $time2->toDateTimeString(); // 2020-05-13 18:00:00
```

#### setTimestamp()

Returns a new instance with the date set to the new timestamp:

```php
<?php

use CodeIgniter\I18n\Time;

// The Application Timezone is "America/Chicago".

$time  = Time::parse('May 10, 2017');
$time2 = $time->setTimestamp(strtotime('April 1, 2017'));

echo $time->toDateTimeString();  // 2017-05-10 00:00:00
echo $time2->toDateTimeString(); // 2017-04-01 00:00:00
```

!!! note "Note"
    Prior to v4.6.0, due to a bug, this method might return an incorrect date/time. See [Upgrading Guide](../installation/upgrade_460.md#timesettimestamp-behavior-fix) for details.

### Modifying the Value

The following methods allow you to modify the date by adding values to or subtracting values from the current Time. This will not modify the existing Time instance, but will return a new instance.

```php
<?php

$time = $time->addSeconds(23);
$time = $time->addMinutes(15);
$time = $time->addHours(12);
$time = $time->addDays(21);
$time = $time->addMonths(14);
$time = $time->addCalendarMonths(2);
$time = $time->addYears(5);

$time = $time->subSeconds(23);
$time = $time->subMinutes(15);
$time = $time->subHours(12);
$time = $time->subDays(21);
$time = $time->subMonths(14);
$time = $time->subCalendarMonths(2);
$time = $time->subYears(5);
```

#### addCalendarMonths() / subCalendarMonths()

Modifies the current Time by adding or subtracting whole calendar months. These methods can be useful if you require that no calendar months be skipped in recurring dates. Refer to the table below for a comparison between `addMonths()` and `addCalendarMonths()` for an initial date of `2025-01-31`.

| $months | addMonths() | addCalendarMonths() |
|---------|-------------|---------------------|
| 1       | 2025-03-03  | 2025-02-28          |
| 2       | 2025-03-31  | 2025-03-31          |
| 3       | 2025-05-01  | 2025-04-30          |
| 4       | 2025-05-31  | 2025-05-31          |
| 5       | 2025-07-01  | 2025-06-30          |
| 6       | 2025-07-31  | 2025-07-31          |

### Comparing Two Times

The following methods allow you to compare one Time instance with another. All comparisons are first converted to UTC before comparisons are done, to ensure that different timezones respond correctly.

#### equals()

Determines if the datetime passed in is equal to the current instance. Equal in this case means that they represent the same moment in time, and are not required to be in the same timezone, as both times are converted to UTC and compared that way:

```php
<?php

use CodeIgniter\I18n\Time;

$time1 = Time::parse('January 10, 2017 21:50:00', 'America/Chicago');
$time2 = Time::parse('January 11, 2017 03:50:00', 'Europe/London');

$time1->equals($time2); // true
```

The value being tested against can be a Time instance, a DateTime instance, or a string with the full date time in a manner that a new DateTime instance can understand. When passing a string as the first parameter, you can pass a timezone string in as the second parameter. If no timezone is given, the system default will be used:

```php
<?php

$time1->equals('January 11, 2017 03:50:00', 'Europe/London'); // true
```

#### sameAs()

This is identical to the `equals()` method, except that it only returns true when the date, time, AND timezone are all identical:

```php
<?php

use CodeIgniter\I18n\Time;

$time1 = Time::parse('January 10, 2017 21:50:00', 'America/Chicago');
$time2 = Time::parse('January 11, 2017 03:50:00', 'Europe/London');

$time1->sameAs($time2); // false
$time2->sameAs('January 10, 2017 21:50:00', 'America/Chicago'); // true
```

#### isBefore()

Checks if the current instance is before the passed-in time. The comparison is done against the UTC versions of both times:

```php
<?php

use CodeIgniter\I18n\Time;

$time1 = Time::parse('January 10, 2017 21:50:00', 'America/Chicago');
$time2 = Time::parse('January 11, 2017 03:50:00', 'America/Chicago');

$time1->isBefore($time2); // true
$time2->isBefore($time1); // false
```

The value being tested against can be a Time instance, a DateTime instance, or a string with the full date time in a manner that a new DateTime instance can understand. When passing a string as the first parameter, you can pass a timezone string in as the second parameter. If no timezone is given, the system default will be used:

```php
<?php

$time1->isBefore('March 15, 2013', 'America/Chicago'); // false
```

#### isAfter()

Works exactly the same as `isBefore()`, except that it checks if the time is after the time passed in:

```php
<?php

use CodeIgniter\I18n\Time;

$time1 = Time::parse('January 10, 2017 21:50:00', 'America/Chicago');
$time2 = Time::parse('January 11, 2017 03:50:00', 'America/Chicago');

$time1->isAfter($time2); // false
$time2->isAfter($time1); // true
```

#### isPast()

!!! success "Available from version 4.7.0"

Determines if the current instance's time is in the past, relative to "now". It returns a boolean true/false:

```php
<?php

use CodeIgniter\I18n\Time;

$time = Time::parse('yesterday');
echo $time->isPast(); // true
```

#### isFuture()

!!! success "Available from version 4.7.0"

Determines if the current instance's time is in the future, relative to "now". It returns a boolean true/false:

```php
<?php

use CodeIgniter\I18n\Time;

$time = Time::parse('tomorrow');
echo $time->isFuture(); // true
```

### Viewing Differences

To compare two Times directly, you would use the `difference()` method, which returns a `CodeIgniter\I18n\TimeDifference` instance.

The first parameter is either a Time instance, a DateTime instance, or a string with the date/time. If a string is passed in the first parameter, the second parameter can be a timezone string:

```php
<?php

use CodeIgniter\I18n\Time;

$time = Time::parse('March 10, 2017', 'America/Chicago');

$diff = $time->difference(Time::now());
$diff = $time->difference(new \DateTime('July 4, 1975', 'America/Chicago'));
$diff = $time->difference('July 4, 1975 13:32:05', 'America/Chicago');
```

Once you have the TimeDifference instance, you have several methods you can use to find information about the difference between the two times. The value returned will be negative if it was in the past, or positive if in the future from the original time:

```php
<?php

use CodeIgniter\I18n\Time;

$current = Time::parse('March 10, 2017', 'America/Chicago');
$test    = Time::parse('March 10, 2010', 'America/Chicago');

$diff = $current->difference($test);

echo $diff->getYears();   // -7
echo $diff->getMonths();  // -84
echo $diff->getWeeks();   // -365
echo $diff->getDays();    // -2557
echo $diff->getHours();   // -61368
echo $diff->getMinutes(); // -3682080
echo $diff->getSeconds(); // -220924800
```

!!! note "Note"
    Prior to v4.4.7, Time always converted the time zones to UTC before comparison. This could lead to unexpected results when the compared period contained a day that differed from 24 hours due to Daylight Saving Time (DST).

    Starting with v4.4.7, when comparing date/times that are in the same time zone, the comparison is performed as is, without conversion to UTC.

    ```php
    <?php

    use CodeIgniter\I18n\Time;

    // 31 Mar 2024 - Daylight Saving Time Starts
    $current = Time::parse('2024-03-31', 'Europe/Madrid');
    $test    = Time::parse('2024-04-01', 'Europe/Madrid');

    $diff = $current->difference($test);

    echo $diff->getDays();
    // 0 in v4.4.6 or before
    // 1 in v4.4.7 or later
    ```

You can use either the `getX()` methods, or access the calculated values as if they were properties:

```php
<?php

echo $diff->years;   // -7
echo $diff->months;  // -84
echo $diff->weeks;   // -365
echo $diff->days;    // -2557
echo $diff->hours;   // -61368
echo $diff->minutes; // -3682080
echo $diff->seconds; // -220924800
```

#### humanize()

Much like Time's `humanize()` method, this returns a string that displays the difference between the times in a human-readable format that is geared towards being easily understood. It can create strings like '3 hours ago', 'in 1 month', etc. The biggest difference is in how very recent dates are handled:

```php
<?php

use CodeIgniter\I18n\Time;

$current = Time::parse('March 10, 2017', 'America/Chicago');
$test    = Time::parse('March 9, 2016 12:00:00', 'America/Chicago');

$diff = $current->difference($test);

echo $diff->humanize(); // 1 year ago
```

The exact time displayed is determined in the following manner:

| Time difference             | Result                         |
|-----------------------------|--------------------------------|
| 1 year < $time < 2 years  | in 1 year / 1 year ago         |
| 1 month < $time < 1 year  | in 6 months / 6 months ago     |
| 7 days < $time < 1 month  | in 3 weeks / 3 weeks ago       |
| today < $time < 7 days    | in 4 days / 4 days ago         |
| 1 hour < $time < 1 day    | in 8 hours / 8 hours ago       |
| 1 minute < $time < 1 hour | in 35 minutes / 35 minutes ago |
| $time < 1 minute           | Now                            |

The result strings come from the language file, **system/Language/en/Time.php**. If you want to overwrite them, create **app/Language/{locale}/Time.php**.
