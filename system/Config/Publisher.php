<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Config;

/**
 * Publisher Configuration
 *
 * Defines basic security restrictions for the Publisher class
 * to prevent abuse by injecting malicious files into a project.
 */
class Publisher extends BaseConfig
{
    /**
     * A list of allowed destinations with a (pseudo-)regex
     * of allowed files for each destination.
     * Attempts to publish to directories not in this list will
     * result in a PublisherException. Files that do no fit the
     * pattern will cause copy/merge to fail.
     *
     * @var array<string,string>
     */
    public $restrictions = [
        ROOTPATH => '*',
        FCPATH   => '#\.(?css|js|map|htm?|xml|json|webmanifest|tff|eot|woff?|gif|jpe?g|tiff?|png|webp|bmp|ico|svg)$#i',
    ];

    /**
     * Disables Registrars to prevent modules from altering the restrictions.
     */
    final protected function registerProperties(): void
    {
    }
}
