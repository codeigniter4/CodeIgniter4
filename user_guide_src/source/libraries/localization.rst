============
Localization
============

.. contents::
    :local:

Introduction
============

CodeIgniter provides several tools to help you localize your application for different languages. While full
localization of an application is a complex subject, it's simple to swap out strings in your application
with different supported languages.

Language strings are stored in the **application/Language** directory, with a sub-directory for each
supported language::

    /application
        /Language
            /en
                app.php
            /fr
                app.php

Configuring the Locale
======================

Every site will have a default language/locale they operate in. This can be set in **Config/App.php**::

    public $defaultLocale = 'en';

The value can be any string that your application uses to manage text strings and other formats. It is
recommended that a [ISO 639-1](https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes) language code, followed
by an underscore (_), and the [ISO 3166-1 alpha-2](https://en.wikipedia.org/wiki/ISO_3166-1#Current_codes)
country code is recommended. This results in language codes like en_US for American English, or fr_FR,
for French/France.

The system is smart enough to fallback to more generic language codes if an exact match
cannot be found. If the locale code was set to **en_US** and we only have language files setup for **en**
then those will be used since nothing exists for the more specific **en_US**. If, however, a language
directory existed at **application/Language/en_US** then that we be used first.

Locale Detection
================

There are two methods supported to detect the correct locale during each request. The first is a "set and forget"
method that will automatically perform :doc:`content negotiation </libraries/content_negotiation>` for you to
determine the correct locale to use. The second method allows you to specify a segment in your routes that
will be used to set the locale.

Content Negotiation
-------------------

You can setup content negotiation to happen automatically by setting two additional settings in Config/App.
The first value tells the Request class that we do want to negotiate a locale, so simply set it to true::

    public $negotiateLocale = true;

Once this is enabled, the system will automatically negotiate the correct language based upon an array
of locales that you have defined in ``$supportLocales``. If no match is found between the languages
that you support, and the requested language, the first item in $supportedLocales will be used. In
the following example, the **en** locale would be used if no match is found::

    public $supportedLocales = ['en', 'es', 'fr_FR'];

In Routes
---------

