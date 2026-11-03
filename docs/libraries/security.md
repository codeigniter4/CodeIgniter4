# Security

The Security Class contains methods that help protect your site against Cross-Site Request Forgery attacks.

## Loading the Library

If your only interest in loading the library is to handle CSRF protection, then you will never need to load it, as it runs as a filter and has no manual interaction.

If you find a case where you do need direct access, though, you may load it through the Services file:

```php
<?php

$security = service('security');
```

## Cross-Site Request Forgery (CSRF)

!!! warning "Warning"
    The CSRF Protection is only available for **POST/PUT/PATCH/DELETE** requests. Requests for other methods are not protected.

### Prerequisite

When you use CodeIgniter's CSRF protection, you still need to code as follows. Otherwise, the CSRF protection may be bypassed.

#### When Auto-Routing is Disabled

Do one of the following:

1.  Do not use `$routes->add()`, and use HTTP verbs in routes.
2.  Check the request method in the controller method before processing.

E.g.:

```
if (! $this->request->is('post')) {
    return $this->response->setStatusCode(405)->setBody('Method Not Allowed');
}
```

!!! note "Note"
    The [$this->request->is()](../incoming/incomingrequest.md#is) method can be used since v4.3.0. In previous versions, you need to use `if (strtolower($this->request->getMethod()) !== 'post')`.

#### When Auto-Routing is Enabled

1.  Check the request method in the controller method before processing.

E.g.:

```
if (! $this->request->is('post')) {
    return $this->response->setStatusCode(405)->setBody('Method Not Allowed');
}
```

### Config for CSRF

#### CSRF Protection Methods

!!! warning "Warning"
    If you use [Session](sessions.md), be sure to use Session-based CSRF protection. Cookie-based CSRF protection will not prevent Same-site attacks. See [GHSA-5hm8-vh6r-2cjq](https://github.com/codeigniter4/shield/security/advisories/GHSA-5hm8-vh6r-2cjq) for details.

By default, the Cookie-based CSRF Protection is used. It is the [Double Submit Cookie](https://cheatsheetseries.owasp.org/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.html#double-submit-cookie) pattern on the OWASP Cross-Site Request Forgery Prevention Cheat Sheet.

You can also use Session-based CSRF Protection. It is the [Synchronizer Token Pattern](https://cheatsheetseries.owasp.org/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.html#synchronizer-token-pattern).

You can use the Session-based CSRF protection by editing the following config parameter value in **app/Config/Security.php**:

```php
<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Security extends BaseConfig
{
    public $csrfProtection = 'session';

    // ...
}
```

#### Token Randomization

To mitigate compression side-channel attacks like [BREACH](https://en.wikipedia.org/wiki/BREACH), and prevent an attacker from guessing the CSRF tokens, you can configure token randomization (off by default).

If you enable it, a random mask is added to the token and used to scramble it.

You can enable it by editing the following config parameter value in **app/Config/Security.php**:

```php
<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Security extends BaseConfig
{
    public $tokenRandomize = true;

    // ...
}
```

#### Token Regeneration

Tokens may be either regenerated on every submission (default) or kept the same throughout the life of the Session or CSRF cookie.

The default regeneration of tokens provides stricter security, but may result in usability concerns as other tokens become invalid (back/forward navigation, multiple tabs/windows, asynchronous actions, etc.). You may alter this behavior by editing the following config parameter value in **app/Config/Security.php**:

```php
<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Security extends BaseConfig
{
    public $regenerate = true;

    // ...
}
```

!!! warning "Warning"
    If you use Cookie-based CSRF protection, and `redirect()` after the submission, you must call `withCookie()` to send the regenerated CSRF cookie. See [Redirect](../outgoing/response.md#redirect) for details.

!!! note "Note"
    Since v4.2.3, you can regenerate the CSRF token manually with the `Security::generateHash()` method.

#### Redirection on Failure

Starting with v4.5.0, when a request fails the CSRF validation check, by default, the user is redirected to the previous page in the production environment, or a SecurityException is thrown in other environments.

!!! note "Note"
    In the production environment, when you use HTML forms, it is recommended to enable this redirection for a better user experience.

    Upgrade users should check their configuration files.

If you want to make it redirect to the previous page, set the following config parameter value to `true` in **app/Config/Security.php**:

```php
<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Security extends BaseConfig
{
    // ...

    public bool $redirect = true;

    // ...
}
```

When redirected, an `error` flash message is set and can be displayed to the end user with the following code in your view:

```
<?= session()->getFlashdata('error') ?>
```

This provides a nicer experience than simply crashing.

Even when the redirect value is `true`, AJAX calls will not redirect, but will throw a SecurityException.

### Enable CSRF Protection

You can enable CSRF protection by altering your **app/Config/Filters.php** and enabling the `csrf` filter globally:

```php
<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Filters extends BaseConfig
{
    public $globals = [
        'before' => [
            // 'honeypot',
            'csrf',
        ],
    ];

    // ...
}
```

Select URIs can be whitelisted from CSRF protection (for example, API endpoints expecting externally POSTed content). You can do this by adding them as exceptions in the filter:

```php
<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Filters extends BaseConfig
{
    public $globals = [
        'before' => [
            'csrf' => ['except' => ['api/record/save']],
        ],
    ];

    // ...
}
```

Regular expressions are also supported (case-insensitive):

```php
<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Filters extends BaseConfig
{
    public $globals = [
        'before' => [
            'csrf' => ['except' => ['api/record/[0-9]+']],
        ],
    ];

    // ...
}
```

It is also possible to enable the CSRF filter only for specific methods:

```php
<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Filters extends BaseConfig
{
    public $methods = [
        'GET'  => ['csrf'],
        'POST' => ['csrf'],
    ];

    // ...
}
```

!!! warning "Warning"
    If you use `$methods` filters, you should [disable Auto Routing (Legacy)](../incoming/routing.md#use-defined-routes-only) because [Auto Routing (Legacy)](../incoming/routing.md#auto-routing-legacy) permits any HTTP method to access a controller. Accessing the controller with a method you don't expect could bypass the filter.

### HTML Forms

If you use the [form helper](../helpers/form_helper.md), then `form_open()` will automatically insert a hidden CSRF field in your forms.

!!! note "Note"
    To use auto-generation of the CSRF field, you need to turn the CSRF filter on for the form page. In most cases, it is requested using the `GET` method.

If not, then you can use the always available `csrf_token()` and `csrf_hash()` functions:

```
<input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
```

Additionally, you can use the `csrf_field()` method to generate this hidden input field for you:

```
// Generates: <input type="hidden" name="{csrf_token}" value="{csrf_hash}" />
<?= csrf_field() ?>
```

When sending a JSON request, the CSRF token can also be passed as one of the parameters. The next way to pass the CSRF token is a special HTTP header whose name is available from the `csrf_header()` function.

Additionally, you can use the `csrf_meta()` method to generate this handy meta tag for you:

```
// Generates: <meta name="{csrf_header}" content="{csrf_hash}" />
<?= csrf_meta() ?>
```

### The Order of Token Sent by Users

The order of checking the availability of the CSRF token is as follows:

1.  `$_POST` array
2.  HTTP header
3.  `php://input` (JSON request) - bear in mind that this approach is the slowest one since we have to decode JSON and then re-encode it
4.  `php://input` (raw body) - for PUT, PATCH, and DELETE type of requests

!!! note "Note"
    `php://input` (raw body) is checked since v4.4.2.

!!! note "Note"
    If you include the CSRF token in a JSON object, removing it decodes and re-encodes the body. This changes its formatting and may affect number precision. To keep the JSON body unchanged, send the token in the `X-CSRF-TOKEN` header and leave the CSRF field out of the body.

## Other Helpful Methods

You will never need to use most of the methods in the Security class directly. The following are methods that you might find helpful that are not related to the CSRF protection.

### sanitizeFilename()

Tries to sanitize filenames in order to prevent directory traversal attempts and other security threats, which is particularly useful for files that were supplied via user input. The first parameter is the path to sanitize.

If it is acceptable for the user input to include relative paths, e.g., **file/in/some/approved/folder.txt**, you can set the second optional parameter, `$relativePath`, to `true`.

```php
<?php

$path = $security->sanitizeFilename($request->getVar('filepath'));
```

This method is an alias for the `sanitize_filename()` function from the Security helper.
