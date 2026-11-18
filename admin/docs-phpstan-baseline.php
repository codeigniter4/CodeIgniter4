<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

/**
 * PHPStan exceptions for the PHP samples of the user guide.
 *
 * An entry is a page relative to the project root, for all of its samples, or `page:line`, for the sample
 * whose opening ```php fence is on that line.
 *
 * - `excludePaths`: samples that are skipped, for errors that PHPStan does not allow to be ignored.
 * - `ignoreErrors`: an error identifier, optionally narrowed by a `message` pattern, ignored in the samples of the
 *   listed `paths` or, without them, in all samples.
 *
 * @return array{
 *     excludePaths: list<string>,
 *     ignoreErrors: list<array{identifier: string, message?: string, paths?: list<string>}>
 * }
 */
return [
    'excludePaths' => [
        'docs/cli/cli_signals.md:201',
        'docs/cli/cli_signals.md:413',
        'docs/cli/cli_signals.md:608',
        'docs/cli/cli_signals.md:654',
        'docs/cli/cli_signals.md:700',
        'docs/concepts/services.md:135',
        'docs/dbmgmt/migration.md:78',
        'docs/extending/core_classes.md:64',
        'docs/guides/api/controller.md:21',
        'docs/guides/first-app/static_pages.md:55',
        'docs/incoming/filters.md:18',
        'docs/incoming/filters.md:59',
        'docs/installation/upgrade_412.md:37',
        'docs/libraries/encryption.md:182',
        'docs/models/entities.md:659',
        'docs/models/model.md:73',
        'docs/models/model.md:562',
        'docs/testing/fabricator.md:9',
    ],
    'ignoreErrors' => [
        // Samples are fragments: they leave out code, and the classes and functions that they refer to.
        ['identifier' => 'class.notFound'],
        ['identifier' => 'codeigniter.configArgumentInstanceof'],
        ['identifier' => 'codeigniter.configArgumentType'],
        ['identifier' => 'codeigniter.modelArgumentType'],
        ['identifier' => 'codeigniter.unknownServiceMethod'],
        ['identifier' => 'expr.resultUnused'],
        ['identifier' => 'function.notFound'],
        ['identifier' => 'method.unused'],
        ['identifier' => 'variable.undefined'],

        // A class that a sample declares, and that several samples declare differently, is resolved to one of them.
        [
            'identifier' => 'method.notFound',
            'message'    => '#^Call to an undefined method (App\\\\|Tests\\\\|SampleCommand::|MyRules::|MyTestClass::)#',
        ],
        [
            'identifier' => 'property.notFound',
            'message'    => '#^Access to an undefined property (App\\\\|Config\\\\|SampleCommand::)#',
        ],

        // A value that PHP coerces, a function or class that the sample does not define, or the Session of CodeIgniter 3.
        [
            'identifier' => 'argument.type',
            'paths'      => [
                'docs/cli/cli_commands.md:78',
                'docs/cli/cli_library.md:240',
                'docs/extending/events.md:25',
                'docs/extending/events.md:50',
                'docs/extending/events.md:89',
                'docs/outgoing/api_responses.md:510',
                'docs/outgoing/api_transformers.md:118',
                'docs/outgoing/api_transformers.md:415',
                'docs/testing/controllers.md:285',
            ],
        ],
        [
            'identifier' => 'method.nonObject',
            'paths'      => [
                'docs/outgoing/api_transformers.md:235',
                'docs/outgoing/api_transformers.md:309',
                'docs/outgoing/api_transformers.md:684',
                'docs/outgoing/view_cells.md:324',
                'docs/outgoing/view_cells.md:347',
            ],
        ],
        [
            'identifier' => 'property.notFound',
            'paths'      => ['docs/installation/upgrade_sessions.md:39'],
        ],

        // Samples that write the array of CodeIgniter 3 or of a configuration in the way that the page describes.
        [
            'identifier' => 'variable.implicitArray',
            'paths'      => [
                'docs/general/configuration.md:245',
                'docs/incoming/routing.md:621',
                'docs/installation/upgrade_localization.md:45',
            ],
        ],

        // Samples that show alternatives one after the other, or code that a sample does not complete.
        [
            'identifier' => 'catch.neverThrown',
            'paths'      => ['docs/cli/cli_commands.md:144', 'docs/general/logging.md:125'],
        ],
        [
            'identifier' => 'deadCode.unreachable',
            'paths'      => [
                'docs/installation/upgrade_localization.md:61',
                'docs/outgoing/localization.md:271',
                'docs/outgoing/localization.md:310',
                'docs/outgoing/localization.md:377',
                'docs/outgoing/response.md:31',
                'docs/outgoing/view_parser.md:273',
            ],
        ],
        [
            'identifier' => 'if.alwaysFalse',
            'paths'      => ['docs/cli/cli_signals.md:527'],
        ],
        [
            'identifier' => 'outOfClass.self',
            'paths'      => ['docs/testing/overview.md:293'],
        ],
        [
            'identifier' => 'property.onlyWritten',
            'paths'      => ['docs/installation/deployment.md:78'],
        ],

        // Samples that use a class that the samples declare differently, or a name that they do not define.
        [
            'identifier' => 'new.noConstructor',
            'paths'      => [
                'docs/concepts/services.md:150',
                'docs/concepts/services.md:204',
                'docs/extending/core_classes.md:81',
                'docs/testing/fabricator.md:50',
            ],
        ],
        [
            'identifier' => 'parameterByRef.type',
            'paths'      => ['docs/libraries/validation.md:1041'],
        ],
        [
            'identifier' => 'return.type',
            'paths'      => [
                'docs/concepts/services.md:150',
                'docs/concepts/services.md:204',
                'docs/extending/core_classes.md:81',
            ],
        ],
        [
            'identifier' => 'property.defaultValue',
            'paths'      => ['docs/outgoing/view_parser.md:518', 'docs/testing/database.md:73'],
        ],
        [
            'identifier' => 'property.nonObject',
            'paths'      => ['docs/testing/fabricator.md:87'],
        ],
        [
            'identifier' => 'property.protected',
            'paths'      => ['docs/libraries/time.md:417'],
        ],
    ],
];
