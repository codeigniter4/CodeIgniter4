############
Localization
############

.. contents::
    :local:
    :depth: 3

********************
Working with Locales
********************

CodeIgniter provides several tools to help you localize your application for different languages. While full
localization of an application is a complex subject, it's simple to swap out strings in your application
with different supported languages.

Configuring the Locale
======================

.. _setting-the-default-locale:

Setting the Default Locale
--------------------------

Every site will have a default language/locale they operate in. This can be set in **app/Config/App.php**:

.. literalinclude:: localization/001.php

The value can be any string that your application uses to manage text strings and other formats. It is
recommended that a `BCP 47 <http://www.rfc-editor.org/rfc/bcp/bcp47.txt>`_ language code is used. This results in
language codes like en-US for American English, or fr-FR, for French/France. A more readable introduction
to this can be found on the `W3C's site <https://www.w3.org/International/articles/language-tags/>`_.

The system is smart enough to fall back to more generic language codes if an exact match
cannot be found. If the locale code was set to ``en-US`` and we only have language files set up for ``en``
then those will be used since nothing exists for the more specific ``en-US``. If, however, a language
directory existed at the **app/Language/en-US** directory then that would be used first.

Locale Detection
================

.. important:: Locale detection only works for web-based requests that use the IncomingRequest class.
    Command-line requests will not have these features.

There are two methods supported to detect the correct locale during the request.

1. `Content Negotiation`_: The first is a "set and forget" method that will automatically perform :doc:`content negotiation </incoming/content_negotiation>` for you to determine the correct locale to use.
2. `In Routes`_: The second method allows you to specify a segment in your routes that will be used to set the locale.

Should you ever need to set the locale directly, see `Setting the Current Locale`_.

Since v4.4.0, ``IncomingRequest::setValidLocales()`` has been added to set
(and reset) valid locales that are set from ``Config\App::$supportedLocales`` setting.

Content Negotiation
-------------------

You can set up content negotiation to happen automatically by setting two additional settings in **app/Config/App.php**.
The first value tells the Request class that we do want to negotiate a locale, so simply set it to true:

.. literalinclude:: localization/002.php

Once this is enabled, the system will automatically negotiate the correct language based upon an array
of locales that you have defined in ``$supportLocales``. If no match is found between the languages
that you support, and the requested language, the first item in ``$supportedLocales`` will be used. In
the following example, the ``en`` locale would be used if no match is found:

.. literalinclude:: localization/003.php

.. _localization-in-routes:

In Routes
---------

The second method uses a custom placeholder to detect the desired locale and set it on the Request. The
placeholder ``{locale}`` can be placed as a segment in your route. If present, the contents of the matching
segment will be your locale:

.. literalinclude:: localization/004.php
    :lines: 2-

In this example, if the user tried to visit **http://example.com/fr/books**, then the locale would be
set to ``fr``, assuming it was configured as a valid locale.

If the value doesn't match a valid locale as defined in ``$supportedLocales`` in **app/Config/App.php**, the default
locale will be used in it's place, unless you set to use only the supported locales defined in the App configuration
file:

.. literalinclude:: localization/018.php
    :lines: 2-

.. note:: The ``useSupportedLocalesOnly()`` method can be used since v4.3.0.

Setting the Current Locale
==========================

IncomingRequest Locale
----------------------

If you want to set the locale directly, you may use the ``setLocale()`` method in
the :doc:`../incoming/incomingrequest`:

.. literalinclude:: localization/020.php
    :lines: 2-

Before setting the locale, you must set valid locales. Because any attempt to
set a locale that are not valid will result in
the :ref:`default locale <setting-the-default-locale>` being set.

By default, the valid locales are defined in ``Config\App::$supportedLocales``
in **app/Config/App.php**:

.. literalinclude:: localization/003.php

.. note:: Since v4.4.0, ``IncomingRequest::setValidLocales()`` has been added to
    set (and reset) valid locales. Use it if you want to change the valid locales
    dynamically.

Language Locale
---------------

The ``Language`` class used in the :php:func:`lang()` function also has the current
locale. This is set to the ``IncomingRequest`` locale during instantiating.

If you want to change the locale after instantiating the language class, use the
``Language::setLocale()`` method.

.. literalinclude:: localization/021.php
    :lines: 2-

Retrieving the Current Locale
=============================

The current locale can always be retrieved from the IncomingRequest object, through the ``getLocale()`` method.
If your controller is extending ``CodeIgniter\Controller``, this will be available through ``$this->request``:

.. literalinclude:: localization/005.php

Alternatively, you can use the :doc:`Services class </concepts/services>` to retrieve the current request:

.. literalinclude:: localization/006.php
    :lines: 2-

.. _language-localization:

*********************
Language Localization
*********************

Creating Language Files
=======================

Language strings are stored in the **app/Language** directory, with a sub-directory for each
supported language (locale)::

    app/
        Language/
            en/
                App.php
            fr/
                App.php

.. note:: The Language Files do not have namespaces.

Languages do not have any specific naming convention that are required. The file should be named logically to
describe the type of content it holds. For example, let's say you want to create a file containing error messages.
You might name it simply: **Errors.php**.

Within the file, you would return an array, where each element in the array has a language key and can have string to return:

.. literalinclude:: localization/007.php

.. note:: You cannot use dots (``.``) at the beginning and end of language keys.

It also support nested definition:

.. literalinclude:: localization/008.php

.. literalinclude:: localization/009.php

Basic Usage
===========

You can use the :php:func:`lang()` helper function to retrieve text from any of the language files, by passing the
filename and the language key as the first parameter, separated by a period (``.``).

For example, to load the ``errorEmailMissing`` string from the **Errors.php**
language file, you would do the following:

.. literalinclude:: localization/010.php
    :lines: 2-

For nested definition, you would do the following:

.. literalinclude:: localization/011.php
    :lines: 2-

If the requested language key doesn't exist in the file for the current locale (after `Language Fallback`_), the string will be passed
back, unchanged. In this example, it would return ``Errors.errorEmailMissing`` or ``Errors.nested.error.message`` if it didn't exist.

Replacing Parameters
--------------------

.. note:: The following functions all require the `intl <https://www.php.net/manual/en/book.intl.php>`_ extension to
    be loaded on your system in order to work. If the extension is not loaded, no replacement will be attempted.
    A great overview can be found over at `Sitepoint <https://www.sitepoint.com/localization-demystified-understanding-php-intl/>`_.

You can pass an array of values to replace placeholders in the language string as the second parameter to the
``lang()`` function. This allows for very simple number translations and formatting:

.. literalinclude:: localization/012.php

The first item in the placeholder corresponds to the index of the item in the array, if it's numerical:

.. literalinclude:: localization/013.php
    :lines: 2-

You can also use named keys to make it easier to keep things straight, if you'd like:

.. literalinclude:: localization/014.php
    :lines: 2-

Obviously, you can do more than just number replacement. According to the
`official ICU docs <https://unicode-org.github.io/icu-docs/apidoc/released/icu4c/classMessageFormat.html#details>`_ for the underlying
library, the following types of data can be replaced:

* numbers - integer, currency, percent
* dates - short, medium, long, full
* time - short, medium, long, full
* spellout - spells out numbers (i.e., 34 becomes thirty-four)
* ordinal
* duration

Here are a few examples:

.. literalinclude:: localization/015.php

You should be sure to read up on the MessageFormatter class and the underlying ICU formatting to get a better
idea on what capabilities it has, like performing the conditional replacement, pluralization, and more. Both of the links provided
earlier will give you an excellent idea as to the options available.

Specifying Locale
-----------------

To specify a different locale to be used when replacing parameters, you can pass the locale in as the
third parameter to the :php:func:`lang()` function.

.. literalinclude:: localization/016.php

If you want to change the current locale, see `Language Locale`_.

Nested Arrays
-------------

Language files also allow nested arrays to make working with lists, etc... easier.

.. literalinclude:: localization/017.php

Language Fallback
=================

If you have a set of messages for a given locale, for instance
**Language/en/App.php**, you can add language variants for that locale,
each in its own folder, for instance **Language/en-US/App.php**.

You only need to provide values for those messages that would be
localized differently for that locale variant. Any missing message
definitions will be automatically pulled from the main locale settings.

It gets better - the localization can fall all the way back to English (**en**),
in case new messages are added to the framework and you haven't had
a chance to translate them yet for your locale.

So, if you are using the locale ``fr-CA``, then a localized
message will first be sought in the **Language/fr-CA** directory, then in
the **Language/fr** directory, and finally in the **Language/en** directory.

System Message Translations
===========================

We have an "official" set of the system message translations in their
`own repository <https://github.com/codeigniter4/translations>`_.

You could download that repository, and copy its **Language** folder
into your **app** folder. The incorporated translations will be automatically
picked up because the ``App`` namespace is mapped to your **app** folder.

Alternately, a better practice would be to run the following command inside your
project:

.. code-block:: console

    composer require codeigniter4/translations

The translated messages will be automatically picked
up because the translations folders get mapped appropriately.

Overriding System Message Translations
======================================

The framework provide `System Message Translations`_, and packages that you
installed may also provide the message translations.

If you want to override some language messages, create language files in the
**app/Language** directory. Then, return only the array you want to override
in the file.

.. _generating-translation-files-via-command:

Generating Translation Files via Command
========================================

.. versionadded:: 4.5.0

You can automatically generate and update translation files in your **app** folder. The command will search for the use of the ``lang()`` function, combine the current translation keys in **app/Language** by defining the locale ``defaultLocale`` from ``Config\App``.
After the operation, you need to translate the language keys yourself.
The command is able to recognize nested keys normally ``File.array.nested.text``.
Previously saved keys do not change.

.. code-block:: console

    php spark lang:find

.. literalinclude:: localization/019.php

.. note:: When the command scans folders, **app/Language** will be skipped.

The language files generated will most likely not conform to your coding standards.
It is recommended to format them. For example, run ``vendor/bin/php-cs-fixer fix ./app/Language`` if ``php-cs-fixer`` is installed.

Before updating, it is possible to preview the translations found by the command:

.. code-block:: console

    php spark lang:find --verbose --show-new

The detailed output of ``--verbose`` also shows a list of invalid keys. For example:

.. code-block:: console

    ...

    Files found: 10
    New translates found: 30
    Bad translates found: 5
    +------------------------+---------------------------------+
    | Bad Key                | Filepath                        |
    +------------------------+---------------------------------+
    | ..invalid_nested_key.. | app/Controllers/Translation.php |
    | .invalid_key           | app/Controllers/Translation.php |
    | TranslationBad         | app/Controllers/Translation.php |
    | TranslationBad.        | app/Controllers/Translation.php |
    | TranslationBad...      | app/Controllers/Translation.php |
    +------------------------+---------------------------------+

    All operations done!

For a more accurate search, specify the desired locale or directory to scan.

.. code-block:: console

    php spark lang:find --dir Controllers/Translation --locale en --show-new

Detailed information can be found by running the command:

.. code-block:: console

    php spark lang:find --help

.. _sync-translations-command:

Synchronization Translation Files via Command
---------------------------------------------

.. versionadded:: 4.6.0

You may need to create files for another language when you've finished translating for the current language. You can use the spark ``lang:find`` command to help with this. However, it might not detect all translations, particularly those with dynamically set parameters like ``lang('App.status.' . $key, ['payload' => 'John'], 'en')``.

To ensure no translations are missed, it's best to copy the completed language files and translate them manually. This approach preserves any unique keys the command might have overlooked.

All you need to do is execute:

.. code-block:: console

    // Specify the locale for new/updated translations
    php spark lang:sync --target ru

    // or set the original locale
    php spark lang:sync --locale en --target ru

As a result, you will receive files with the translation keys.
If there were duplicate keys in the target locale, they are saved.

.. warning:: Non-matching keys in new translations are deleted!
