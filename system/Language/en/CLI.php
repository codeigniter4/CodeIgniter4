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

// CLI language settings
return [
    'altCommandPlural'   => 'Did you mean one of these?',
    'altCommandSingular' => 'Did you mean this?',
    'argumentPrompt'     => 'Please provide a value for the "{0}" argument',
    'commandAlias'       => '[alias of {0}]',
    'commandNotFound'    => 'Command "{0}" not found.',
    'generator'          => [
        'cancelOperation' => 'Operation has been cancelled.',
        'className'       => [
            'cell'        => 'Cell class name',
            'command'     => 'Command class name',
            'config'      => 'Config class name',
            'controller'  => 'Controller class name',
            'default'     => 'Class name',
            'entity'      => 'Entity class name',
            'filter'      => 'Filter class name',
            'request'     => 'FormRequest class name',
            'migration'   => 'Migration class name',
            'model'       => 'Model class name',
            'seeder'      => 'Seeder class name',
            'test'        => 'Test class name',
            'transformer' => 'Transformer class name',
            'validation'  => 'Validation class name',
        ],
        'commandType'      => 'Command type',
        'confirmContinue'  => 'Are you sure you want to continue?',
        'databaseGroup'    => 'Database group',
        'fileCreate'       => 'File created: {0}',
        'fileError'        => 'Error while creating file: "{0}"',
        'fileExist'        => 'File exists: "{0}"',
        'fileOverwrite'    => 'File overwritten: "{0}"',
        'invalidClassName' => 'Class name "{0}" is not valid.',
        'parentClass'      => 'Parent class',
        'returnType'       => 'Return type',
        'tableName'        => 'Table name',
        'usingCINamespace' => 'Warning: Using the "CodeIgniter" namespace will generate the file in the system directory.',
        'viewName'         => [
            'cell' => 'Cell view name',
        ],
    ],
    'helpAliases'           => 'Aliases:',
    'helpArguments'         => 'Arguments:',
    'helpAvailableCommands' => 'Available commands:',
    'helpDescription'       => 'Description:',
    'helpOptions'           => 'Options:',
    'helpUsage'             => 'Usage:',
    'invalidColor'          => 'Invalid "{0}" color: "{1}".',
    'namespaceNotDefined'   => 'Namespace "{0}" is not defined.',
    'signals'               => [
        'noPcntlExtension' => 'PCNTL extension not available. Signal handling disabled.',
        'noPosixExtension' => 'SIGTSTP/SIGCONT handling requires POSIX extension. These signals will be removed from registration.',
        'failedSignal'     => 'Failed to register handler for signal: "{0}".',
    ],
];
