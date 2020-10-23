<?php

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2019 British Columbia Institute of Technology
 * Copyright (c) 2019-2020 CodeIgniter Foundation
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package    CodeIgniter
 * @author     CodeIgniter Dev Team
 * @copyright  2019-2020 CodeIgniter Foundation
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link       https://codeigniter.com
 * @since      Version 4.0.0
 * @filesource
 */

// Email language settings
return [
   'mustBeArray'          => 'The email validation method must be passed an array.',
   'invalidAddress'       => 'Invalid email address: {0}',
   'attachmentMissing'    => 'Unable to locate the following email attachment: {0}',
   'attachmentUnreadable' => 'Unable to open this attachment: {0}',
   'noFrom'               => 'Cannot send mail with no "From" header.',
   'noRecipients'         => 'You must include recipients: To, Cc, or Bcc',
   'sendFailurePHPMail'   => 'Unable to send email using PHP mail(). Your server might not be configured to send mail using this method.',
   'sendFailureSendmail'  => 'Unable to send email using PHP Sendmail. Your server might not be configured to send mail using this method.',
   'sendFailureSmtp'      => 'Unable to send email using PHP SMTP. Your server might not be configured to send mail using this method.',
   'sent'                 => 'Your message has been successfully sent using the following protocol: {0}',
   'noSocket'             => 'Unable to open a socket to Sendmail. Please check settings.',
   'noHostname'           => 'You did not specify a SMTP hostname.',
   'SMTPError'            => 'The following SMTP error was encountered: {0}',
   'noSMTPAuth'           => 'Error: You must assign a SMTP username and password.',
   'failedSMTPLogin'      => 'Failed to send AUTH LOGIN command. Error: {0}',
   'SMTPAuthUsername'     => 'Failed to authenticate username. Error: {0}',
   'SMTPAuthPassword'     => 'Failed to authenticate password. Error: {0}',
   'SMTPDataFailure'      => 'Unable to send data: {0}',
   'exitStatus'           => 'Exit status code: {0}',
];
