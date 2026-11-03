# Localization

## Working with Locales

CodeIgniter provides several tools to help you localize your application for different languages. While full localization of an application is a complex subject, it's simple to swap out strings in your application with different supported languages.

### Configuring the Locale

#### Setting the Default Locale

Every site will have a default language/locale it operates in. This can be set in **app/Config/App.php**:

```php
<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class App extends BaseConfig
{
    // ...

    public string $defaultLocale = 'en';

    // ...
}
```

The value can be any string that your application uses to manage text strings and other formats. It is recommended that a [BCP 47](http://www.rfc-editor.org/rfc/bcp/bcp47.txt) language code is used. This results in language codes like en-US for American English, or fr-FR for French/France. A more readable introduction to this can be found on the [W3C's site](https://www.w3.org/International/articles/language-tags/).

The system is smart enough to fall back to more generic language codes if an exact match cannot be found. If the locale code was set to `en-US` and we only have language files set up for `en`, then those will be used since nothing exists for the more specific `en-US`. If, however, a language directory existed at **app/Language/en-US**, then that would be used first.

### Locale Detection

!!! important "Important"
    Locale detection only works for web-based requests that use the IncomingRequest class. Command-line requests will not have these features.

There are two methods supported to detect the correct locale during the request.

1.  [Content Negotiation](#content-negotiation): The first is a "set and forget" method that will automatically perform [content negotiation](../incoming/content_negotiation.md) for you to determine the correct locale to use.
2.  [In Routes](#in-routes): The second method allows you to specify a segment in your routes that will be used to set the locale.

Should you ever need to set the locale directly, see [Setting the Current Locale](#setting-the-current-locale).

Since v4.4.0, `IncomingRequest::setValidLocales()` has been added to set (and reset) valid locales that are set from `Config\App::$supportedLocales` setting.

#### Content Negotiation

You can set up content negotiation to happen automatically by setting two additional settings in **app/Config/App.php**. The first value tells the Request class that we do want to negotiate a locale, so simply set it to true:

```php
<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class App extends BaseConfig
{
    // ...

    public bool $negotiateLocale = true;

    // ...
}
```

Once this is enabled, the system will automatically negotiate the correct language based upon an array of locales that you have defined in `$supportedLocales`. If no match is found between the languages that you support and the requested language, the first item in `$supportedLocales` will be used. In the following example, the `en` locale would be used if no match is found:

```php
<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class App extends BaseConfig
{
    // ...

    public array $supportedLocales = ['en', 'es', 'fr-FR'];

    // ...
}
```

#### In Routes

The second method uses a custom placeholder to detect the desired locale and set it on the Request. The placeholder `{locale}` can be placed as a segment in your route. If present, the contents of the matching segment will be your locale:

```php
$routes->get('{locale}/books', 'App\Books::index');
```

In this example, if the user tried to visit **http://example.com/fr/books**, then the locale would be set to `fr`, assuming it was configured as a valid locale.

If the value doesn't match a valid locale as defined in `$supportedLocales` in **app/Config/App.php**, the default locale will be used in its place, unless you choose to use only the supported locales defined in the App configuration file:

```php
$routes->useSupportedLocalesOnly(true);
```

!!! note "Note"
    The `useSupportedLocalesOnly()` method can be used since v4.3.0.

### Setting the Current Locale

#### IncomingRequest Locale

If you want to set the locale directly, you may use the `setLocale()` method in the [IncomingRequest Class](../incoming/incomingrequest.md):

```php
/** @var \CodeIgniter\HTTP\IncomingRequest $request */
$request->setLocale('ja');
```

Before setting the locale, you must set valid locales, because any attempt to set a locale that is not valid will result in the [default locale](#setting-the-default-locale) being set.

By default, the valid locales are defined in `Config\App::$supportedLocales` in **app/Config/App.php**:

```php
<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class App extends BaseConfig
{
    // ...

    public array $supportedLocales = ['en', 'es', 'fr-FR'];

    // ...
}
```

!!! note "Note"
    Since v4.4.0, `IncomingRequest::setValidLocales()` has been added to set (and reset) valid locales. Use it if you want to change the valid locales dynamically.

#### Language Locale

The `Language` class used in the `lang()` function also has the current locale. This is set to the `IncomingRequest` locale during instantiation.

If you want to change the locale after instantiating the language class, use the `Language::setLocale()` method.

```php
/** @var \CodeIgniter\Language\Language $lang */
$lang = service('language');
$lang->setLocale('ja');
```

### Retrieving the Current Locale

The current locale can always be retrieved from the IncomingRequest object, through the `getLocale()` method. If your controller is extending `CodeIgniter\Controller`, this will be available through `$this->request`:

```php
<?php

namespace App\Controllers;

class UserController extends BaseController
{
    public function index()
    {
        $locale = $this->request->getLocale();
    }
}
```

Alternatively, you can use the [Services class](../concepts/services.md) to retrieve the current request:

```php
$locale = service('request')->getLocale();
```

## Language Localization

### Creating Language Files

Language strings are stored in the **app/Language** directory, with a sub-directory for each supported language (locale):

```
app/
    Language/
        en/
            App.php
        fr/
            App.php
```

!!! note "Note"
    The Language Files do not have namespaces.

Languages do not have any specific naming convention that is required. The file should be named logically to describe the type of content it holds. For example, let's say you want to create a file containing error messages. You might name it simply: **Errors.php**.

Within the file, you would return an array, where each element in the array has a language key and can have a string to return:

```php
<?php

return [
    'languageKey' => 'The actual message to be shown.',
];
```

!!! note "Note"
    You cannot use dots (`.`) at the beginning and end of language keys.

It also supports nested definitions:

```php
<?php

return [
    'languageKey' => [
        'nested' => [
            'key' => 'The actual message to be shown.',
        ],
    ],
];
```

```php
<?php

return [
    'errorEmailMissing'    => 'You must submit an email address',
    'errorURLMissing'      => 'You must submit a URL',
    'errorUsernameMissing' => 'You must submit a username',
    'nested'               => [
        'error' => [
            'message' => 'A specific error message',
        ],
    ],
];
```

### Basic Usage

You can use the `lang()` helper function to retrieve text from any of the language files, by passing the filename and the language key as the first parameter, separated by a period (`.`).

For example, to load the `errorEmailMissing` string from the **Errors.php** language file, you would do the following:

```php
echo lang('Errors.errorEmailMissing');
```

For nested definitions, you would do the following:

```php
echo lang('Errors.nested.error.message');
```

If the requested language key doesn't exist in the file for the current locale (after [Language Fallback](#language-fallback)), the string will be passed back, unchanged. In this example, it would return `Errors.errorEmailMissing` or `Errors.nested.error.message` if it didn't exist.

#### Replacing Parameters

!!! note "Note"
    The following functions all require the [intl](https://www.php.net/manual/en/book.intl.php) extension to be loaded on your system in order to work. If the extension is not loaded, no replacement will be attempted. A great overview can be found over at [Sitepoint](https://www.sitepoint.com/localization-demystified-understanding-php-intl/).

You can pass an array of values to replace placeholders in the language string as the second parameter to the `lang()` function. This allows for very simple number translations and formatting:

```php
<?php

// The language file, Tests.php:
return [
    'apples'      => 'I have {0, number} apples.',
    'men'         => 'The top {1, number} men out-performed the remaining {0, number}',
    'namedApples' => 'I have {number_apples, number, integer} apples.',
];

// Displays "I have 3 apples."
echo lang('Tests.apples', [3]);
```

The first item in the placeholder corresponds to the index of the item in the array, if it's numerical:

```php
// Displays "The top 23 men out-performed the remaining 20"
echo lang('Tests.men', [20, 23]);
```

You can also use named keys to make it easier to keep things straight, if you'd like:

```php
// Displays "I have 3 apples."
echo lang('Tests.namedApples', ['number_apples' => 3]);
```

Obviously, you can do more than just number replacement. According to the [official ICU docs](https://unicode-org.github.io/icu-docs/apidoc/released/icu4c/classMessageFormat.html#details) for the underlying library, the following types of data can be replaced:

- numbers - integer, currency, percent
- dates - short, medium, long, full
- time - short, medium, long, full
- spellout - spells out numbers (i.e., 34 becomes thirty-four)
- ordinal
- duration

Here are a few examples:

```php
<?php

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
```

You should be sure to read up on the MessageFormatter class and the underlying ICU formatting to get a better idea of what capabilities it has, like performing conditional replacement, pluralization, and more. Both of the links provided earlier will give you an excellent idea as to the options available.

#### Specifying Locale

To specify a different locale to be used when replacing parameters, you can pass the locale in as the third parameter to the `lang()` function.

```php
<?php

// Displays "The time is now 23:21:28 GMT-5"
echo lang('Test.longTime', [time()], 'ru-RU');

// Displays "£7.41"
echo lang('{price, number, currency}', ['price' => 7.41], 'en-GB');
// Displays "$7.41"
echo lang('{price, number, currency}', ['price' => 7.41], 'en-US');
```

If you want to change the current locale, see [Language Locale](#language-locale).

#### Nested Arrays

Language files also allow nested arrays to make working with lists, etc. easier.

```php
<?php

// Language/en/Fruit.php

return [
    'list' => [
        'Apples',
        'Bananas',
        'Grapes',
        'Lemons',
        'Oranges',
        'Strawberries',
    ],
];

// Displays "Apples, Bananas, Grapes, Lemons, Oranges, Strawberries"
echo implode(', ', lang('Fruit.list'));
```

### Language Fallback

If you have a set of messages for a given locale, for instance **Language/en/App.php**, you can add language variants for that locale, each in its own folder, for instance **Language/en-US/App.php**.

You only need to provide values for those messages that would be localized differently for that locale variant. Any missing message definitions will be automatically pulled from the main locale settings.

It gets better - the localization can fall all the way back to English (**en**), in case new messages are added to the framework and you haven't had a chance to translate them yet for your locale.

So, if you are using the locale `fr-CA`, then a localized message will first be sought in the **Language/fr-CA** directory, then in the **Language/fr** directory, and finally in the **Language/en** directory.

### System Message Translations

We have an "official" set of the system message translations in their [own repository](https://github.com/codeigniter4/translations).

You could download that repository, and copy its **Language** folder into your **app** folder. The incorporated translations will be automatically picked up because the `App` namespace is mapped to your **app** folder.

Alternatively, a better practice would be to run the following command inside your project:

```console
composer require codeigniter4/translations
```

The translated messages will be automatically picked up because the translations folders get mapped appropriately.

### Overriding System Message Translations

The framework provides [System Message Translations](#system-message-translations), and packages that you installed may also provide the message translations.

If you want to override some language messages, create language files in the **app/Language** directory. Then, return only the array you want to override in the file.

### Generating Translation Files via Command

!!! success "Available from version 4.5.0"

You can automatically generate and update translation files in your **app** folder. The command will search for uses of the `lang()` function and combine them with the current translation keys in **app/Language**, using the `defaultLocale` locale from `Config\App`. After the operation, you need to translate the language keys yourself. The command can recognize nested keys such as `File.array.nested.text`. Previously saved keys do not change.

!!! note "Note"
    Keys that `lang()` can already resolve (for example `Errors.*` from **system/Language**) are treated as already translated, so they are neither listed by `--show-new` nor written into **app/Language**.

```console
php spark lang:find
```

```php
<?php

// Controllers/Translation/Lang.php
$message  = lang('Text.info.success');
$message2 = lang('Text.paragraph');

// The following will be saved in Language/en/Text.php
return [
    'info' => [
        'success' => 'Text.info.success',
    ],
    'paragraph' => 'Text.paragraph',
];
```

!!! note "Note"
    When the command scans folders, **app/Language** will be skipped.

The language files generated will most likely not conform to your coding standards. It is recommended to format them. For example, run `vendor/bin/php-cs-fixer fix ./app/Language` if `php-cs-fixer` is installed.

Before updating, it is possible to preview the translations found by the command:

```console
php spark lang:find --verbose --show-new
```

The detailed output of `--verbose` also shows a list of invalid keys. For example:

```console
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
```

For a more accurate search, specify the desired locale or directory to scan.

```console
php spark lang:find --dir Controllers/Translation --locale en --show-new
```

Detailed information can be found by running the command:

```console
php spark lang:find --help
```

#### Synchronizing Translation Files via Command

!!! success "Available from version 4.6.0"

You may need to create files for another language when you've finished translating for the current language. You can use the spark `lang:find` command to help with this. However, it might not detect all translations, particularly those with dynamically set parameters like `lang('App.status.' . $key, ['payload' => 'John'], 'en')`.

To ensure no translations are missed, it's best to copy the completed language files and translate them manually. This approach preserves any unique keys the command might have overlooked.

All you need to do is execute:

```console
// Specify the locale for new/updated translations
php spark lang:sync --target ru

// or set the original locale
php spark lang:sync --locale en --target ru
```

As a result, you will receive files with the translation keys. If there were duplicate keys in the target locale, they are saved.

!!! warning "Warning"
    Non-matching keys in new translations are deleted!
