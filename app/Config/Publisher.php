<?php

namespace Config;

use CodeIgniter\Config\Publisher as BasePublisher;

/**
 * Publisher Configuration
 *
 * Defines basic security restrictions for the Publisher class
 * to prevent abuse by injecting malicious files into a project.
 */
class Publisher extends BasePublisher
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
}
