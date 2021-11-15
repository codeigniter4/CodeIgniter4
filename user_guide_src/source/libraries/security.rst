########
Security
########

The Security Class contains methods that help protect your site against Cross-Site Request Forgery attacks.

.. contents::
    :local:
    :depth: 3

*******************
Loading the Library
*******************

If your only interest in loading the library is to handle CSRF protection, then you will never need to load it,
as it runs as a filter and has no manual interaction.

If you find a case where you do need direct access though, you may load it through the Services file::

    $security = \Config\Services::security();

*********************************
Cross-site request forgery (CSRF)
*********************************

.. warning:: The CSRF Protection is only available for **POST/PUT/PATCH/DELETE** requests.
    Requests for other methods are not protected.

Config for CSRF
===============

CSRF Protection Methods
-----------------------

By default, the Cookie based CSRF Protection is used. It is
`Double Submit Cookie <https://cheatsheetseries.owasp.org/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.html#double-submit-cookie>`_
on OWASP Cross-Site Request Forgery Prevention Cheat Sheet.

You can also use Session based CSRF Protection. It is
`Synchronizer Token Pattern <https://cheatsheetseries.owasp.org/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.html#synchronizer-token-pattern>`_.

You can set to use the Session based CSRF protection by editing the following config parameter value in
**app/Config/Security.php**::

    public $csrfProtection = 'session';

Token Randomization
-------------------

To mitigate compression side-channel attacks like `BREACH`_, and prevent an attacker from guessing the CSRF tokens, you can configure token randomization (off by default).

If you enable it, a random mask is added to the token and used to scramble it.

.. _`BREACH`: https://en.wikipedia.org/wiki/BREACH

You can enable it by editing the following config parameter value in
**app/Config/Security.php**::

    public $tokenRandomize = true;

Token Regeneration
------------------

Tokens may be either regenerated on every submission (default) or
kept the same throughout the life of the CSRF cookie. The default
regeneration of tokens provides stricter security, but may result
in usability concerns as other tokens become invalid (back/forward
navigation, multiple tabs/windows, asynchronous actions, etc). You
may alter this behavior by editing the following config parameter value in
**app/Config/Security.php**::

    public $regenerate  = true;

Redirection on Failure
----------------------

When a request fails the CSRF validation check, it will redirect to the previous page by default,
setting an ``error`` flash message that you can display to the end user with the following code in your view::

    <?= session()->getFlashdata('error') ?>

This provides a nicer experience
than simply crashing. This can be turned off by editing the following config parameter value in
**app/Config/Security.php**::

    public $redirect = false;

Even when the redirect value is ``true``, AJAX calls will not redirect, but will throw an error.

Enable CSRF Protection
======================

You can enable CSRF protection by altering your **app/Config/Filters.php**
and enabling the `csrf` filter globally::

    public $globals = [
        'before' => [
            // 'honeypot',
            'csrf',
        ],
    ];

Select URIs can be whitelisted from CSRF protection (for example API
endpoints expecting externally POSTed content). You can add these URIs
by adding them as exceptions in the filter::

    public $globals = [
        'before' => [
            'csrf' => ['except' => ['api/record/save']],
        ],
    ];

Regular expressions are also supported (case-insensitive)::

    public $globals = [
        'before' => [
            'csrf' => ['except' => ['api/record/[0-9]+']],
        ],
    ];

It is also possible to enable the CSRF filter only for specific methods::

    public $methods = [
        'get'  => ['csrf'],
        'post' => ['csrf'],
    ];

HTML Forms
==========

If you use the :doc:`form helper <../helpers/form_helper>`, then
:func:`form_open()` will automatically insert a hidden csrf field in
your forms.

.. note:: To use auto-generation of CSRF field, you need to turn CSRF filter on to the form page.
    In most cases it is requested using the ``GET`` method.

If not, then you can use the always available ``csrf_token()``
and ``csrf_hash()`` functions
::

    <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />

Additionally, you can use the ``csrf_field()`` method to generate this
hidden input field for you::

    // Generates: <input type="hidden" name="{csrf_token}" value="{csrf_hash}" />
    <?= csrf_field() ?>

When sending a JSON request the CSRF token can also be passed as one of the parameters.
The next way to pass the CSRF token is a special Http header that's name is available by
``csrf_header()`` function.

Additionally, you can use the ``csrf_meta()`` method to generate this handy
meta tag for you::

    // Generates: <meta name="{csrf_header}" content="{csrf_hash}" />
    <?= csrf_meta() ?>

The Order of Token Sent by Users
================================

The order of checking the availability of the CSRF token is as follows:

1. ``$_POST`` array
2. HTTP header
3. ``php://input`` (JSON request) - bear in mind that this approach is the slowest one since we have to decode JSON and then re-encode it

*********************
Other Helpful Methods
*********************

You will never need to use most of the methods in the Security class directly. The following are methods that
you might find helpful that are not related to the CSRF protection.

**sanitizeFilename()**

Tries to sanitize filenames in order to prevent directory traversal attempts and other security threats, which is
particularly useful for files that were supplied via user input. The first parameter is the path to sanitize.

If it is acceptable for the user input to include relative paths, e.g., **file/in/some/approved/folder.txt**, you can set
the second optional parameter, ``$relativePath`` to ``true``.
::

    $path = $security->sanitizeFilename($request->getVar('filepath'));
