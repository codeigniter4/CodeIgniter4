###########
Email Class
###########

CodeIgniter's robust Email Class supports the following features:

-  Multiple Protocols: Mail, Sendmail, and SMTP
-  TLS and SSL Encryption for SMTP
-  Multiple recipients
-  CC and BCCs
-  HTML or Plaintext email
-  Attachments
-  Word wrapping
-  Priorities
-  BCC Batch Mode, enabling large email lists to be broken into small
   BCC batches.
-  Email Debugging tools

.. contents::
    :local:
    :depth: 3

***********************
Using the Email Library
***********************

Sending Email
=============

Sending email is not only simple, but you can configure it on the fly or
set your preferences in the **app/Config/Email.php** file.

Here is a basic example demonstrating how you might send email:

.. literalinclude:: email/001.php

.. _setting-email-preferences:

Setting Email Preferences
=========================

There are 21 different preferences available to tailor how your email
messages are sent. You can either set them manually as described here,
or automatically via preferences stored in your config file, described
below:

Setting Email Preferences by Passing an Array
---------------------------------------------

Preferences are set by passing an array of preference values to the
email initialize method. Here is an example of how you might set some
preferences:

.. literalinclude:: email/002.php

.. note:: Most of the preferences have default values that will be used
    if you do not set them.

Setting Email Preferences in a Config File
------------------------------------------

If you prefer not to set preferences using the above method, you can
instead put them into the config file. Simply open the
**app/Config/Email.php** file, and set your configs in the
Email properties. Then save the file and it will be used automatically.
You will NOT need to use the ``$email->initialize()`` method if
you set your preferences in the config file.

.. _email-ssl-tls-for-smtp:

SSL versus TLS for SMTP Protocol
--------------------------------

To protect the username, password and email content while communicating with the SMTP server,
encryption on the channel should be used. Two different standards are widely deployed and
it is important to understand the differences when trying to troubleshoot email sending
issues.

Most SMTP servers allow connections on ports 465 or 587 when submitting emails. (The
original port 25 is seldom used because of many ISPs have blocking rules in place and
since the communication is entirely in clear-text).

The key difference is that port 465 expects the communication channel to be secured using TLS
from the start as per `RFC 8314 <https://tools.ietf.org/html/rfc8314>`_.
A connection to port 587 allows clear-text connection and later
will upgrade the channel to use encryption using the ``STARTTLS`` SMTP command.

Upgrading a connection on port 465 may or may not be supported by the server, so the
``STARTTLS`` SMTP command may fail if the server does not allow it. If you set the port to 465,
you should try to set the ``SMTPCrypto`` to an empty string (``''``) since the communication is
secured using TLS from the start and the ``STARTTLS`` is not needed.

If your configuration requires you to connect to port 587, you should most likely set
``SMTPCrypto`` to ``tls`` as this will implement the ``STARTTLS`` command while communicating
with the SMTP server to switch from clear-text to an encrypted channel. The initial communication
will be made in clear-text and the channel will be upgraded to TLS with the ``STARTTLS`` command.

Reviewing Preferences
---------------------

The settings used for the last successful send are available from the
instance property ``$archive``. This is helpful for testing and debugging
to determine that actual values at the time of the ``send()`` call.

Email Preferences
=================

The following is a list of all the preferences that can be set when
sending email.

=================== ====================== ============================ =======================================================================
Preference          Default Value          Options                      Description
=================== ====================== ============================ =======================================================================
**userAgent**       CodeIgniter            None                         The "user agent".
**protocol**        mail                   mail, sendmail, or smtp      The mail sending protocol.
**mailPath**        /usr/sbin/sendmail     None                         The server path to Sendmail.
**SMTPHost**        No Default             None                         SMTP Server Address.
**SMTPUser**        No Default             None                         SMTP Username.
**SMTPPass**        No Default             None                         SMTP Password.
**SMTPPort**        25                     None                         SMTP Port. (If set to ``465``, TLS will be used for the connection
                                                                        regardless of ``SMTPCrypto`` setting.)
**SMTPTimeout**     5                      None                         SMTP Timeout (in seconds).
**SMTPKeepAlive**   false                  true or false (boolean)      Enable persistent SMTP connections.
**SMTPCrypto**      tls                    tls, ssl, or empty string    SMTP Encryption. Setting this to ``ssl`` will create a secure
                                                                        channel to the server using SSL, and ``tls`` will issue a
                                                                        ``STARTTLS`` command to the server. Connection on port 465 should
                                                                        set this to an empty string (``''``). See also
                                                                        :ref:`email-ssl-tls-for-smtp`.
**wordWrap**        true                   true or false (boolean)      Enable word-wrap.
**wrapChars**       76                                                  Character count to wrap at.
**mailType**        text                   text or html                 Type of mail. If you send HTML email you must send it as a complete web
                                                                        page. Make sure you don't have any relative links or relative image
                                                                        paths otherwise they will not work.
**charset**         utf-8                                               Character set (utf-8, iso-8859-1, etc.).
**validate**        true                   true or false (boolean)      Whether to validate the email address.
**priority**        3                      1, 2, 3, 4, 5                Email Priority. 1 = highest. 5 = lowest. 3 = normal.
**CRLF**            \\n                    "\\r\\n" or "\\n" or "\\r"   Newline character. (Use "\\r\\n" to comply with RFC 822).
**newline**         \\n                    "\\r\\n" or "\\n" or "\\r"   Newline character. (Use "\\r\\n" to comply with RFC 822).
**BCCBatchMode**    false                  true or false (boolean)      Enable BCC Batch Mode.
**BCCBatchSize**    200                    None                         Number of emails in each BCC batch.
**DSN**             false                  true or false (boolean)      Enable notify message from server
=================== ====================== ============================ =======================================================================

Overriding Word Wrapping
========================

If you have word wrapping enabled (recommended to comply with RFC 822)
and you have a very long link in your email it can get wrapped too,
causing it to become un-clickable by the person receiving it.
CodeIgniter lets you manually override word wrapping within part of your
message like this::

    The text of your email that
    gets wrapped normally.

    {unwrap}http://example.com/a_long_link_that_should_not_be_wrapped.html{/unwrap}

    More text that will be
    wrapped normally.

Place the item you do not want word-wrapped between: {unwrap} {/unwrap}

***************
Class Reference
***************

.. php:namespace:: CodeIgniter\Email

.. php:class:: Email

    .. php:method:: setFrom($from[, $name = ''[, $returnPath = null]])

        :param    string    $from: "From" e-mail address
        :param    string    $name: "From" display name
        :param    string    $returnPath: Optional email address to redirect undelivered e-mail to
        :returns:    CodeIgniter\\Email\\Email instance (method chaining)
        :rtype:    CodeIgniter\\Email\\Email

        Sets the email address and name of the person sending the email:

        .. literalinclude:: email/003.php

        You can also set a Return-Path, to help redirect undelivered mail:

        .. literalinclude:: email/004.php

        .. note:: Return-Path can't be used if you've configured 'smtp' as
            your protocol.

    .. php:method:: setReplyTo($replyto[, $name = ''])

        :param    string    $replyto: E-mail address for replies
        :param    string    $name: Display name for the reply-to e-mail address
        :returns:    CodeIgniter\\Email\\Email instance (method chaining)
        :rtype:    CodeIgniter\\Email\\Email

        Sets the reply-to address. If the information is not provided the
        information in the `setFrom <#setFrom>`_ method is used. Example:

        .. literalinclude:: email/005.php

    .. php:method:: setTo($to)

        :param    mixed    $to: Comma-delimited string or an array of e-mail addresses
        :returns:    CodeIgniter\\Email\\Email instance (method chaining)
        :rtype:    CodeIgniter\\Email\\Email

        Sets the email address(s) of the recipient(s). Can be a single e-mail,
        a comma-delimited list or an array:

        .. literalinclude:: email/006.php

        .. literalinclude:: email/007.php

        .. literalinclude:: email/008.php

    .. php:method:: setCC($cc)

        :param    mixed    $cc: Comma-delimited string or an array of e-mail addresses
        :returns:    CodeIgniter\\Email\\Email instance (method chaining)
        :rtype:    CodeIgniter\\Email\\Email

        Sets the CC email address(s). Just like the "to", can be a single e-mail,
        a comma-delimited list or an array.

    .. php:method:: setBCC($bcc[, $limit = ''])

        :param    mixed    $bcc: Comma-delimited string or an array of e-mail addresses
        :param    int    $limit: Maximum number of e-mails to send per batch
        :returns:    CodeIgniter\\Email\\Email instance (method chaining)
        :rtype:    CodeIgniter\\Email\\Email

        Sets the BCC email address(s). Just like the ``setTo()`` method, can be a single
        e-mail, a comma-delimited list or an array.

        If ``$limit`` is set, "batch mode" will be enabled, which will send
        the emails to batches, with each batch not exceeding the specified
        ``$limit``.

    .. php:method:: setSubject($subject)

        :param    string    $subject: E-mail subject line
        :returns:    CodeIgniter\\Email\\Email instance (method chaining)
        :rtype:    CodeIgniter\\Email\\Email

        Sets the email subject:

        .. literalinclude:: email/009.php

    .. php:method:: setMessage($body)

        :param    string    $body: E-mail message body
        :returns:    CodeIgniter\\Email\\Email instance (method chaining)
        :rtype:    CodeIgniter\\Email\\Email

        Sets the e-mail message body:

        .. literalinclude:: email/010.php

    .. php:method:: setAltMessage($str)

        :param    string    $str: Alternative e-mail message body
        :returns:    CodeIgniter\\Email\\Email instance (method chaining)
        :rtype:    CodeIgniter\\Email\\Email

        Sets the alternative e-mail message body:

        .. literalinclude:: email/011.php

        This is an optional message string which can be used if you send
        HTML formatted email. It lets you specify an alternative message
        with no HTML formatting which is added to the header string for
        people who do not accept HTML email. If you do not set your own
        message CodeIgniter will extract the message from your HTML email
        and strip the tags.

    .. php:method:: setHeader($header, $value)

        :param    string    $header: Header name
        :param    string    $value: Header value
        :returns:    CodeIgniter\\Email\\Email instance (method chaining)
        :rtype: CodeIgniter\\Email\\Email

        Appends additional headers to the e-mail:

        .. literalinclude:: email/012.php

    .. php:method:: clear($clearAttachments = false)

        :param    bool    $clearAttachments: Whether or not to clear attachments
        :returns:    CodeIgniter\\Email\\Email instance (method chaining)
        :rtype: CodeIgniter\\Email\\Email

        Initializes all the email variables to an empty state. This method
        is intended for use if you run the email sending method in a loop,
        permitting the data to be reset between cycles.

        .. literalinclude:: email/013.php

        If you set the parameter to true any attachments will be cleared as
        well:

        .. literalinclude:: email/014.php

    .. php:method:: send($autoClear = true)

        :param    bool    $autoClear: Whether to clear message data automatically
        :returns:    true on success, false on failure
        :rtype:    bool

        The e-mail sending method. Returns boolean true or false based on
        success or failure, enabling it to be used conditionally:

        .. literalinclude:: email/015.php

        This method will automatically clear all parameters if the request was
        successful. To stop this behaviour pass false:

        .. literalinclude:: email/016.php

        .. note:: In order to use the ``printDebugger()`` method, you need
            to avoid clearing the email parameters.

        .. note:: If ``BCCBatchMode`` is enabled, and there are more than
            ``BCCBatchSize`` recipients, this method will always return
            boolean ``true``.

    .. php:method:: attach($filename[, $disposition = ''[, $newname = null[, $mime = '']]])

        :param    string    $filename: File name
        :param    string    $disposition: 'disposition' of the attachment. Most
            email clients make their own decision regardless of the MIME
            specification used here. https://www.iana.org/assignments/cont-disp/cont-disp.xhtml
        :param    string    $newname: Custom file name to use in the e-mail
        :param    string    $mime: MIME type to use (useful for buffered data)
        :returns:    CodeIgniter\\Email\\Email instance (method chaining)
        :rtype:    CodeIgniter\\Email\\Email

        Enables you to send an attachment. Put the file path/name in the first
        parameter. For multiple attachments use the method multiple times.
        For example:

        .. literalinclude:: email/017.php

        To use the default disposition (attachment), leave the second parameter blank,
        otherwise use a custom disposition:

        .. literalinclude:: email/018.php

        You can also use a URL:

        .. literalinclude:: email/019.php

        If you'd like to use a custom file name, you can use the third parameter:

        .. literalinclude:: email/020.php

        If you need to use a buffer string instead of a real - physical - file you can
        use the first parameter as buffer, the third parameter as file name and the fourth
        parameter as mime-type:

        .. literalinclude:: email/021.php

    .. php:method:: setAttachmentCID($filename)

        :param    string    $filename: Existing attachment filename
        :returns:    Attachment Content-ID or false if not found
        :rtype:    string

        Sets and returns an attachment's Content-ID, which enables you to embed an inline
        (picture) attachment into HTML. First parameter must be the already attached file name.

        .. literalinclude:: email/022.php

        .. note:: Content-ID for each e-mail must be re-created for it to be unique.

    .. php:method:: printDebugger($include = ['headers', 'subject', 'body'])

        :param    array    $include: Which parts of the message to print out
        :returns:    Formatted debug data
        :rtype:    string

        Returns a string containing any server messages, the email headers, and
        the email message. Useful for debugging.

        You can optionally specify which parts of the message should be printed.
        Valid options are: **headers**, **subject**, **body**.

        Example:

        .. literalinclude:: email/023.php

        .. note:: By default, all of the raw data will be printed.
