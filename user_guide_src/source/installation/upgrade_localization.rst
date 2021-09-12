Upgrade Localization
####################

.. contents::
    :local:
    :depth: 1


Documentations
==============

- `Language Documentation Codeigniter 3.X <http://codeigniter.com/userguide3/libraries/language.html>`_
- :doc:`Localization Documentation Codeigniter 4.X </outgoing/localization>`


What has been changed
=====================
- In CI4 the language files return the language lines as array.

Upgrade Guide
=============
1. Specify the default language in *Config/App.php*:::

    public $defaultLocale = 'en';

2. Now move your language files to ``app/Language/<locale>/``.
3. After that you have to change the syntax within the language files. Below in the Code Example you will see how the language array within the file should look like.
4. Remove from every file the language loader ``$this->lang->load($file, $lang);``.
5. Replace the method to load the language line ``$this->lang->line('error_email_missing')`` with ``echo lang('Errors.errorEmailMissing');``.

Code Example
============

Codeigniter Version 3.11
------------------------
::

    // error.php
    $lang['error_email_missing']    = 'You must submit an email address';
    $lang['error_url_missing']      = 'You must submit a URL';
    $lang['error_username_missing'] = 'You must submit a username';

    ...

    $this->lang->load('error', $lang);
    echo $this->lang->line('error_email_missing');

Codeigniter Version 4.x
-----------------------
::

    // Errors.php
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

    ...

    echo lang('Errors.errorEmailMissing');
