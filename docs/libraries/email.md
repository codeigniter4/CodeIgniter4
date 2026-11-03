# Email Class

CodeIgniter's robust Email Class supports the following features:

- Multiple Protocols: Mail, Sendmail, and SMTP
- TLS and SSL Encryption for SMTP
- Multiple recipients
- CC and BCCs
- HTML or Plaintext email
- Attachments
- Word wrapping
- Priorities
- BCC Batch Mode, enabling large email lists to be broken into small BCC batches.
- Email Debugging tools

## Using the Email Library

### Sending Email

Sending email is not only simple, but you can configure it on the fly or set your preferences in the **app/Config/Email.php** file.

Here is a basic example demonstrating how you might send email:

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

### Setting Email Preferences

There are several preferences available to tailor how your email messages are sent. You can either set them manually as described here, or automatically via preferences stored in your config file, described in [Email Preferences](#email-preferences).

#### Setting Email Preferences by Passing an Array

Preferences are set by passing an array of preference values to the email initialize method. Here is an example of how you might set some preferences:

```php
<?php

$config['protocol'] = 'sendmail';
$config['mailPath'] = '/usr/sbin/sendmail';
$config['charset']  = 'iso-8859-1';
$config['wordWrap'] = true;

$email->initialize($config);
```

!!! note "Note"
    Most of the preferences have default values that will be used if you do not set them.

#### Setting Email Preferences in a Config File

If you prefer not to set preferences using the above method, you can instead put them into the config file. Simply open the **app/Config/Email.php** file, and set your configs in the Email properties. Then save the file and it will be used automatically. You will NOT need to use the `$email->initialize()` method if you set your preferences in the config file.

#### SSL versus TLS for SMTP Protocol

To protect the username, password and email content while communicating with the SMTP server, encryption on the channel should be used. Two different standards are widely deployed and it is important to understand the differences when trying to troubleshoot email sending issues.

Most SMTP servers allow connections on ports 465 or 587 when submitting emails. (The original port 25 is seldom used because many ISPs have blocking rules in place and because the communication is entirely in clear text.)

The key difference is that port 465 expects the communication channel to be secured using TLS from the start as per [RFC 8314](https://tools.ietf.org/html/rfc8314). A connection to port 587 allows a clear-text connection and later will upgrade the channel to use encryption using the `STARTTLS` SMTP command.

Upgrading a connection on port 465 may or may not be supported by the server, so the `STARTTLS` SMTP command may fail if the server does not allow it. If you set the port to 465, you should try to set `SMTPCrypto` to an empty string (`''`) since the communication is secured using TLS from the start and `STARTTLS` is not needed.

If your configuration requires you to connect to port 587, you should most likely set `SMTPCrypto` to `tls` as this will implement the `STARTTLS` command while communicating with the SMTP server to switch from clear text to an encrypted channel. The initial communication will be made in clear text and the channel will be upgraded to TLS with the `STARTTLS` command.

#### Reviewing Preferences

The settings used for the last successful send are available from the instance property `$archive`. This is helpful for testing and debugging to determine the actual values at the time of the `send()` call.

### Email Preferences

The following is a list of all the preferences that can be set when sending email.

| Preference | Default Value | Options | Description |
|----|----|----|----|
| **fromEmail** |  |  | The email address to be set in the "from" header. |
| **fromName** |  |  | The name to be set in the "from" header. |
| **userAgent** | CodeIgniter |  | The "user agent". |
| **protocol** | mail | `mail`, `sendmail`, or `smtp` | The mail sending protocol. |
| **mailPath** | /usr/sbin/sendmail |  | The server path to Sendmail. |
| **SMTPHost** |  |  | SMTP Server Hostname. |
| **SMTPAuthMethod** | login | `login`, `plain` | SMTP Authentication Method. (Available since 4.7.0) |
| **SMTPUser** |  |  | SMTP Username. |
| **SMTPPass** |  |  | SMTP Password. |
| **SMTPPort** | 25 |  | SMTP Port. (If set to `465`, TLS will be used for the connection regardless of the `SMTPCrypto` setting.) |
| **SMTPTimeout** | 5 |  | SMTP Timeout (in seconds). |
| **SMTPKeepAlive** | false | `true`/`false` | Enable persistent SMTP connections. |
| **SMTPCrypto** | tls | `tls`, `ssl`, or empty string (`''`) | SMTP Encryption. Setting this to `ssl` will create a secure channel to the server using SSL, and `tls` will issue a `STARTTLS` command to the server. Connections on port `465` should set this to an empty string (`''`). See also [SSL versus TLS for SMTP Protocol](#ssl-versus-tls-for-smtp-protocol). |
| **wordWrap** | true | `true`/`false` | Enable word-wrap. |
| **wrapChars** | 76 |  | Character count to wrap at. |
| **mailType** | text | `text` or `html` | Type of mail. If you send HTML email you must send it as a complete web page. Make sure you don't have any relative links or relative image paths, otherwise they will not work. |
| **charset** | UTF-8 |  | Character set (`utf-8`, `iso-8859-1`, etc.). |
| **validate** | false | `true`/`false` | Whether to validate the email address. |
| **priority** | 3 | 1, 2, 3, 4, 5 | Email Priority. `1` = highest. `5` = lowest. `3` = normal. |
| **CRLF** | \r\n | `\r\n`, `\n` or `\r` | Newline character. (Use `\r\n` to comply with RFC 822). |
| **newline** | \r\n | `\r\n`, `\n` or `\r` | Newline character. (Use `\r\n` to comply with RFC 822). |
| **BCCBatchMode** | false | `true`/`false` | Enable BCC Batch Mode. |
| **BCCBatchSize** | 200 |  | Number of emails in each BCC batch. |
| **DSN** | false | `true`/`false` | Enable notification messages from the server. |

The defaults above reflect **app/Config/Email.php**, which is used by the Email service. If you instantiate `CodeIgniter\Email\Email` directly without a configuration object, its `$validate` property defaults to `true`.

### Overriding Word Wrapping

If you have word wrapping enabled (recommended to comply with RFC 822) and you have a very long link in your email, it can get wrapped too, causing it to become unclickable by the person receiving it. CodeIgniter lets you manually override word wrapping within part of your message like this:

```
The text of your email that
gets wrapped normally.

{unwrap}http://example.com/a_long_link_that_should_not_be_wrapped.html{/unwrap}

More text that will be
wrapped normally.
```

Place the item you do not want word-wrapped between: {unwrap} {/unwrap}

## Class Reference

## CodeIgniter\Email

### Email

#### setFrom($from[, $name = ''[, $returnPath = null]])

- **Parameters**  
    - **$from** `string` "From" email address
    - **$name** `string` "From" display name
    - **$returnPath** `string` Optional email address to redirect undelivered email to

- **Returns**: CodeIgniter\Email\Email instance (method chaining)

- **Return type**: `CodeIgniter\Email\Email`

Sets the email address and name of the person sending the email:

```php
<?php

$email->setFrom('you@example.com', 'Your Name');
```

You can also set a Return-Path, to help redirect undelivered mail:

```php
<?php

$email->setFrom('you@example.com', 'Your Name', 'returned_emails@example.com');
```

!!! note "Note"
    Return-Path can't be used if you've configured 'smtp' as your protocol.

#### setReplyTo($replyto[, $name = ''])

- **Parameters**  
    - **$replyto** `string` Email address for replies
    - **$name** `string` Display name for the reply-to email address

- **Returns**: CodeIgniter\Email\Email instance (method chaining)

- **Return type**: `CodeIgniter\Email\Email`

Sets the reply-to address. If the information is not provided, the information in the `setFrom()` method is used. Example:

```php
<?php

$email->setReplyTo('you@example.com', 'Your Name');
```

#### setTo($to)

- **Parameters**  
    - **$to** `mixed` Comma-separated string or an array of email addresses

- **Returns**: CodeIgniter\Email\Email instance (method chaining)

- **Return type**: `CodeIgniter\Email\Email`

Sets the email address(es) of the recipient(s). Can be a single email, a comma-separated list, or an array:

```php
<?php

$email->setTo('someone@example.com');
```

```php
<?php

$email->setTo('one@example.com, two@example.com, three@example.com');
```

```php
<?php

$email->setTo(['one@example.com', 'two@example.com', 'three@example.com']);
```

#### setCC($cc)

- **Parameters**  
    - **$cc** `mixed` Comma-separated string or an array of email addresses

- **Returns**: CodeIgniter\Email\Email instance (method chaining)

- **Return type**: `CodeIgniter\Email\Email`

Sets the CC email address(es). Just like the "to" address, it can be a single email, a comma-separated list, or an array.

#### setBCC($bcc[, $limit = ''])

- **Parameters**  
    - **$bcc** `mixed` Comma-separated string or an array of email addresses
    - **$limit** `int` Maximum number of emails to send per batch

- **Returns**: CodeIgniter\Email\Email instance (method chaining)

- **Return type**: `CodeIgniter\Email\Email`

Sets the BCC email address(es). Just like the `setTo()` method, it can be a single email, a comma-separated list, or an array.

If `$limit` is set, "batch mode" will be enabled, which will send the emails in batches, with each batch not exceeding the specified `$limit`.

#### setSubject($subject)

- **Parameters**  
    - **$subject** `string` Email subject line

- **Returns**: CodeIgniter\Email\Email instance (method chaining)

- **Return type**: `CodeIgniter\Email\Email`

Sets the email subject:

```php
<?php

$email->setSubject('This is my subject');
```

#### setMessage($body)

- **Parameters**  
    - **$body** `string` Email message body

- **Returns**: CodeIgniter\Email\Email instance (method chaining)

- **Return type**: `CodeIgniter\Email\Email`

Sets the email message body:

```php
<?php

$email->setMessage('This is my message');
```

#### setAltMessage($str)

- **Parameters**  
    - **$str** `string` Alternative email message body

- **Returns**: CodeIgniter\Email\Email instance (method chaining)

- **Return type**: `CodeIgniter\Email\Email`

Sets the alternative email message body:

```php
<?php

$email->setAltMessage('This is the alternative message');
```

This is an optional message string which can be used if you send HTML-formatted email. It lets you specify an alternative message with no HTML formatting which is added to the header string for people who do not accept HTML email. If you do not set your own message, CodeIgniter will extract the message from your HTML email and strip the tags.

#### setHeader($header, $value)

- **Parameters**  
    - **$header** `string` Header name
    - **$value** `string` Header value

- **Returns**: CodeIgniter\Email\Email instance (method chaining)

- **Return type**: `CodeIgniter\Email\Email`

Appends additional headers to the email:

```php
<?php

$email->setHeader('Header1', 'Value1');
$email->setHeader('Header2', 'Value2');
```

#### clear($clearAttachments = false)

- **Parameters**  
    - **$clearAttachments** `bool` Whether or not to clear attachments

- **Returns**: CodeIgniter\Email\Email instance (method chaining)

- **Return type**: `CodeIgniter\Email\Email`

Initializes all the email variables to an empty state. This method is intended for use if you run the email sending method in a loop, permitting the data to be reset between cycles.

```php
<?php

foreach ($list as $name => $address) {
    $email->clear();

    $email->setTo($address);
    $email->setFrom('your@example.com');
    $email->setSubject('Here is your info ' . $name);
    $email->setMessage('Hi ' . $name . ' Here is the info you requested.');
    $email->send();
}
```

If you set the parameter to true, any attachments will be cleared as well:

```php
<?php

$email->clear(true);
```

#### send($autoClear = true)

- **Parameters**  
    - **$autoClear** `bool` Whether to clear message data automatically

- **Returns**: true on success, false on failure

- **Return type**: `bool`

The email sending method. Returns boolean true or false based on success or failure, enabling it to be used conditionally:

```php
<?php

if (! $email->send()) {
    // Generate error
}
```

This method will automatically clear all parameters if the request was successful. To stop this behaviour, pass false:

```php
<?php

if ($email->send(false)) {
    // Parameters won't be cleared
}
```

!!! note "Note"
    In order to use the `printDebugger()` method, you need to avoid clearing the email parameters.

!!! note "Note"
    If `BCCBatchMode` is enabled, and there are more than `BCCBatchSize` recipients, this method will always return boolean `true`.

#### attach($filename[, $disposition = ''[, $newname = null[, $mime = '']]])

- **Parameters**  
    - **$filename** `string` File name
    - **$disposition** `string` 'disposition' of the attachment. Most email clients make their own decision regardless of the MIME specification used here. <https://www.iana.org/assignments/cont-disp/cont-disp.xhtml>
    - **$newname** `string` Custom file name to use in the email
    - **$mime** `string` MIME type to use (useful for buffered data)

- **Returns**: CodeIgniter\Email\Email instance (method chaining)

- **Return type**: `CodeIgniter\Email\Email`

Enables you to send an attachment. Put the file path/name in the first parameter. For multiple attachments, use the method multiple times. For example:

```php
<?php

$email->attach('/path/to/photo1.jpg');
$email->attach('/path/to/photo2.jpg');
$email->attach('/path/to/photo3.jpg');
```

To use the default disposition (attachment), leave the second parameter blank. Otherwise, use a custom disposition:

```php
<?php

$email->attach('image.jpg', 'inline');
```

You can also use a URL:

```php
<?php

$email->attach('http://example.com/filename.pdf');
```

If you'd like to use a custom file name, you can use the third parameter:

```php
<?php

$email->attach('filename.pdf', 'attachment', 'report.pdf');
```

If you need to use a buffer string instead of a real (physical) file, you can use the first parameter as the buffer, the third parameter as the file name, and the fourth parameter as the MIME type:

```php
<?php

$email->attach($buffer, 'attachment', 'report.pdf', 'application/pdf');
```

#### setAttachmentCID($filename)

- **Parameters**  
    - **$filename** `string` Existing attachment filename

- **Returns**: Attachment Content-ID or false if not found

- **Return type**: `string`

Sets and returns an attachment's Content-ID, which enables you to embed an inline (picture) attachment into HTML. The first parameter must be the already attached file name.

```php
<?php

$filename = '/img/photo1.jpg';
$email->attach($filename);

foreach ($list as $address) {
    $email->setTo($address);
    $cid = $email->setAttachmentCID($filename);
    $email->setMessage('<img src="cid:' . $cid . '" alt="photo1">');
    $email->send();
}
```

!!! note "Note"
    Content-ID for each email must be re-created for it to be unique.

#### printDebugger($include = ['headers', 'subject', 'body'])

- **Parameters**  
    - **$include** `array` Which parts of the message to print out

- **Returns**: Formatted debug data

- **Return type**: `string`

Returns a string containing any server messages, the email headers, and the email message. Useful for debugging.

You can optionally specify which parts of the message should be printed. Valid options are: **headers**, **subject**, **body**.

Example:

```php
<?php

// You need to pass false while sending in order for the email data
// to not be cleared - if that happens, printDebugger() would have
// nothing to output.
$email->send(false);

// Will only print the email headers, excluding the message subject and body
$email->printDebugger(['headers']);
```

!!! note "Note"
    By default, all of the raw data will be printed.
