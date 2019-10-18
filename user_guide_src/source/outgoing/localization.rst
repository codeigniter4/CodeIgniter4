############
Localization
############

.. contents::
    :local:
    :depth: 2

********************
Working With Locales
********************

CodeIgniter provides several tools to help you localize your application for different languages. While full
localization of an application is a complex subject, it's simple to swap out strings in your application
with different supported languages.

Language strings are stored in the **app/Language** directory, with a sub-directory for each
supported language::

    /app
        /Language
            /en
                app.php
            /fr
                app.php

.. important:: Locale detection only works for web-based requests that use the IncomingRequest class.
    Command-line requests will not have these features.

Configuring the Locale
======================

Every site will have a default language/locale they operate in. This can be set in **Config/App.php**::

    public $defaultLocale = 'en';

The value can be any string that your application uses to manage text strings and other formats. It is
recommended that a `BCP 47 <http://www.rfc-editor.org/rfc/bcp/bcp47.txt>`_ language code is used. This results in
language codes like en-US for American English, or fr-FR, for French/France. A more readable introduction
to this can be found on the `W3C's site <https://www.w3.org/International/articles/language-tags/>`_.

The system is smart enough to fall back to more generic language codes if an exact match
cannot be found. If the locale code was set to **en-US** and we only have language files set up for **en**
then those will be used since nothing exists for the more specific **en-US**. If, however, a language
directory existed at **app/Language/en-US** then that would be used first.

Locale Detection
================

There are two methods supported to detect the correct locale during the request. The first is a "set and forget"
method that will automatically perform :doc:`content negotiation </incoming/content_negotiation>` for you to
determine the correct locale to use. The second method allows you to specify a segment in your routes that
will be used to set the locale.

Content Negotiation
-------------------

You can set up content negotiation to happen automatically by setting two additional settings in Config/App.
The first value tells the Request class that we do want to negotiate a locale, so simply set it to true::

    public $negotiateLocale = true;

Once this is enabled, the system will automatically negotiate the correct language based upon an array
of locales that you have defined in ``$supportLocales``. If no match is found between the languages
that you support, and the requested language, the first item in $supportedLocales will be used. In
the following example, the **en** locale would be used if no match is found::

    public $supportedLocales = ['en', 'es', 'fr-FR'];

In Routes
---------

The second method uses a custom placeholder to detect the desired locale and set it on the Request. The
placeholder ``{locale}`` can be placed as a segment in your route. If present, the contents of the matching
segment will be your locale::

    $routes->get('{locale}/books', 'App\Books::index');

In this example, if the user tried to visit ``http://example.com/fr/books``, then the locale would be
set to ``fr``, assuming it was configured as a valid locale.

.. note:: If the value doesn't match a valid locale as defined in the App configuration file, the default
    locale will be used in it's place.

Retrieving the Current Locale
=============================

The current locale can always be retrieved from the IncomingRequest object, through the ``getLocale()`` method.
If your controller is extending ``CodeIgniter\Controller``, this will be available through ``$this->request``::

    <?php namespace App\Controllers;

    class UserController extends \CodeIgniter\Controller
    {
        public function index()
        {
            $locale = $this->request->getLocale();
        }
    }

Alternatively, you can use the :doc:`Services class </concepts/services>` to retrieve the current request::

    $locale = service('request')->getLocale();

*********************
Language Localization
*********************

Creating Language Files
=======================

Languages do not have any specific naming convention that are required. The file should be named logically to
describe the type of content it holds. For example, let's say you want to create a file containing error messages.
You might name it simply: **Errors.php**.

Within the file, you would return an array, where each element in the array has a language key and the string to return::

        'language_key' => 'The actual message to be shown.'

.. note:: It's good practice to use a common prefix for all messages in a given file to avoid collisions with
    similarly named items in other files. For example, if you are creating error messages you might prefix them
    with error\_

::

    return [
        'errorEmailMissing'    => 'You must submit an email address',
        'errorURLMissing'      => 'You must submit a URL',
        'errorUsernameMissing' => 'You must submit a username',
    ];

Basic Usage
===========

You can use the ``lang()`` helper function to retrieve text from any of the language files, by passing the
filename and the language key as the first parameter, separated by a period (.). For example, to load the
``errorEmailMissing`` string from the ``Errors`` language file, you would do the following::

    echo lang('Errors.errorEmailMissing');

If the requested language key doesn't exist in the file for the current locale, the string will be passed
back, unchanged. In this example, it would return 'Errors.errorEmailMissing' if it didn't exist.

Replacing Parameters
--------------------

.. note:: The following functions all require the `intl <http://php.net/manual/en/book.intl.php>`_ extension to
    be loaded on your system in order to work. If the extension is not loaded, no replacement will be attempted.
    A great overview can be found over at `Sitepoint <https://www.sitepoint.com/localization-demystified-understanding-php-intl/>`_.

You can pass an array of values to replace placeholders in the language string as the second parameter to the
``lang()`` function. This allows for very simple number translations and formatting::

    // The language file, Tests.php:
    return [
        "apples"      => "I have {0, number} apples.",
        "men"         => "I have {1, number} men out-performed the remaining {0, number}",
        "namedApples" => "I have {number_apples, number, integer} apples.",
    ];

    // Displays "I have 3 apples."
    echo lang('Tests.apples', [ 3 ]);

The first item in the placeholder corresponds to the index of the item in the array, if it's numerical::

    // Displays "The top 23 men out-performed the remaining 20"
    echo lang('Tests.men', [20, 23]);

You can also use named keys to make it easier to keep things straight, if you'd like::

    // Displays "I have 3 apples."
    echo lang("Tests.namedApples", ['number_apples' => 3]);

Obviously, you can do more than just number replacement. According to the
`official ICU docs <http://icu-project.org/apiref/icu4c/classMessageFormat.html#details>`_ for the underlying
library, the following types of data can be replaced:

* numbers - integer, currency, percent
* dates - short, medium, long, full
* time - short, medium, long, full
* spellout - spells out numbers (i.e. 34 becomes thirty-four)
* ordinal
* duration

Here are a few examples::

    // The language file, Tests.php
    return [
        'shortTime'  => 'The time is now {0, time, short}.',
        'mediumTime' => 'The time is now {0, time, medium}.',
        'longTime'   => 'The time is now {0, time, long}.',
        'fullTime'   => 'The time is now {0, time, full}.',
        'shortDate'  => 'The date is now {0, date, short}.',
        'mediumDate' => 'The date is now {0, date, medium}.',
        'longDate'   => 'The date is now {0, date, long}.',
        'fullDate'   => 'The date is now {0, date, full}.',
        'spelledOut' => '34 is {0, spellout}',
        'ordinal'    => 'The ordinal is {0, ordinal}',
        'duration'   => 'It has been {0, duration}',
    ];

    // Displays "The time is now 11:18 PM"
    echo lang('Tests.shortTime', [time()]);
    // Displays "The time is now 11:18:50 PM"
    echo lang('Tests.mediumTime', [time()]);
    // Displays "The time is now 11:19:09 PM CDT"
    echo lang('Tests.longTime', [time()]);
    // Displays "The time is now 11:19:26 PM Central Daylight Time"
    echo lang('Tests.fullTime', [time()]);

    // Displays "The date is now 8/14/16"
    echo lang('Tests.shortDate', [time()]);
    // Displays "The date is now Aug 14, 2016"
    echo lang('Tests.mediumDate', [time()]);
    // Displays "The date is now August 14, 2016"
    echo lang('Tests.longDate', [time()]);
    // Displays "The date is now Sunday, August 14, 2016"
    echo lang('Tests.fullDate', [time()]);

    // Displays "34 is thirty-four"
    echo lang('Tests.spelledOut', [34]);

    // Displays "It has been 408,676:24:35"
    echo lang('Tests.ordinal', [time()]);

You should be sure to read up on the MessageFormatter class and the underlying ICU formatting to get a better
idea on what capabilities it has, like performing the conditional replacement, pluralization, and more. Both of the links provided
earlier will give you an excellent idea as to the options available.

Specifying Locale
-----------------

To specify a different locale to be used when replacing parameters, you can pass the locale in as the
third parameter to the ``lang()`` method.
::

    // Displays "The time is now 23:21:28 GMT-5"
    echo lang('Test.longTime', [time()], 'ru-RU');

    // Displays "£7.41"
    echo lang('{price, number, currency}', ['price' => 7.41], 'en-GB');
    // Displays "$7.41"
    echo lang('{price, number, currency}', ['price' => 7.41], 'en-US');

Nested Arrays
-------------

Language files also allow nested arrays to make working with lists, etc... easier.
::

    // Language/en/Fruit.php

    return [
        'list' => [
            'Apples',
            'Bananas',
            'Grapes',
            'Lemons',
            'Oranges',
            'Strawberries'
        ]
    ];

    // Displays "Apples, Bananas, Grapes, Lemons, Oranges, Strawberries"
    echo implode(', ', lang('Fruit.list'));

Language Fallback
=================

If you have a set of messages for a given locale, for instance
``Language/en/app.php``, you can add language variants for that locale,
each in its own folder, for instance ``Language/en-US/app.php``.

You only need to provide values for those messages that would be
localized differently for that locale variant. Any missing message
definitions will be automatically pulled from the main locale settings.

It gets better - the localization can fall all the way back to English,
in case new messages are added to the framework and you haven't had
a chance to translate them yet for your locale.

So, if you are using the locale ``fr-CA``, then a localized
message will first be sought in ``Language/fr/CA``, then in
``Language/fr``, and finally in ``Language/en``.

Message Translations
====================

We have an "official" set of translations in their
`own repository <https://github.com/codeigniter4/translations>`_.

You could download that repository, and copy its ``Language`` folder
into your ``app``. The incorporated translations will be automatically
picked up because the ``App`` namespace is mapped to your ``app`` folder.

Alternately, a better practice would be to ``composer install codeigniter4/translations``
inside your project, and the translated messages will be automatically picked
up because the translations folders get mapped appropriately.
