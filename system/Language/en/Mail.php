<?php

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2017, British Columbia Institute of Technology
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
 * @package	CodeIgniter
 * @author	CodeIgniter Dev Team
 * @copyright	Copyright (c) 2014 - 2017, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	http://codeigniter.com
 * @since	Version 3.0.0
 * @filesource
 */

return [
	'invalidGroup' => '%s is not a valid Mail group.',
    'invalidHandlerName' => '%s is not a valid Mail Handler.',
    'invalidHandler' => 'Unable to send message. No valid Handler provided.',
    'emptyMessage' => 'Mail messages must have a body, from, and to addresses specified.',
    'errorWritingFile' => 'Unable to write email message to disk: %s',
    'mustBeArray' => 'The email validation method must be passed an array.',
    'invalidEmail' => 'Invalid email address: %s',
    'noFrom' => 'Cannot send mail with no "From" header.',
    'noRecipients' => 'You must include recipients: To, Cc, or Bcc',
    'sendFailurePhpmail' => 'Unable to send email using PHP mail(). Your server might not be configured to send mail using this method',
    'sendFailureSendmail' => 'Unable to send email using PHP Sendmail. Your server might not be configured to send mail using this method.',
    'sendFailureSmtp' => 'Unable to send email using PHP SMTP. Your server might not be configured to send mail using this method.',
    'sent' => 'Your message has been successfully sent using the following protocol: ',
    'smtpError' => 'The following SMTP error was encountered: ',
    'noSMTPUnpw' => 'Error: You must assign a SMTP username and password.',
    'failedSMTPLogin' => 'Failed to send AUTH LOGIN command. Error: ',
    'smtpAuthUn' => 'Failed to authenticate username. Error: ',
    'smtpAuthPw' => 'Failed to authenticate password. Error: ',
    'smtpDataFailure' => 'Unable to send data: ',
    'exitStatus' => 'Exit status code: ',
];
