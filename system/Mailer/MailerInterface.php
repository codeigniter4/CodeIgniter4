<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Mailer;

use Config\Mailer;

/**
 * Mailer Interface
 *
 * Defines the methods and types for a Mailer class to handle sending emails.
 * It is highly recommended that any interface implementations extend
 * \CodeIgniter\Mailer\Handlers\BaseHandler which fulfills most of these
 * methods and provides a lot of additional email utility.
 */
interface MailerInterface
{
    /**
     * Stores the Mailer config.
     */
    public function __construct(Mailer $config);

    /**
     * Whether this handler is supported on this system.
     */
    public function isSupported(): bool;

    /**
     * Sends an Email.
     */
    public function send(Email $email);
}
