# Upgrade Emails

## Documentation

- [Email Documentation CodeIgniter 3.x](http://codeigniter.com/userguide3/libraries/email.html)
- [Email Documentation CodeIgniter 4.x](../libraries/email.md)

## What has been changed

- Only small things like the method names and the loading of the library have changed.
- The behavior when using the SMTP protocol has been slightly changed. You may not be able to communicate with your SMTP server properly if you use the CI3 settings. See [SSL versus TLS for SMTP Protocol](../libraries/email.md#ssl-versus-tls-for-smtp-protocol) and [Email Preferences](../libraries/email.md#email-preferences).

## Upgrade Guide

1.  Within your class, change `$this->load->library('email');` to `$email = service('email');`.
2.  From then on, you have to change `$this->email` to `$email` in every line starting with it.
3.  The methods in the Email class are named slightly differently. All methods, except for `send()`, `attach()`, `printDebugger()` and `clear()`, have `set` as a prefix followed by the previous method name. `bcc()` is now `setBcc()` and so on.
4.  The config attributes in **app/Config/Email.php** have changed. You should have a look at the [Setting Email Preferences](../libraries/email.md#setting-email-preferences) for a list of the new attributes.

## Code Example

### CodeIgniter Version 3.x

```php
<?php

$this->load->library('email');

$this->email->from('your@example.com', 'Your Name');
$this->email->to('someone@example.com');
$this->email->cc('another@another-example.com');
$this->email->bcc('them@their-example.com');

$this->email->subject('Email Test');
$this->email->message('Testing the email class.');

$this->email->send();
```

### CodeIgniter Version 4.x

```php
<?php

$email = service('email');

$email->setFrom('your@example.com', 'Your Name');
$email->setTo('someone@example.com');
$email->setCC('another@another-example.com');
$email->setBCC('them@their-example.com');

$email->setSubject('Email Test');
$email->setMessage('Testing the email class.');

$email->send();
```
