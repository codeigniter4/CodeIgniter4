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

// Commands language settings
return [
    'arrayArgumentInvalidDefault'           => 'Array argument "{0}" must have an array default value or null.',
    'arrayArgumentCannotBeRequired'         => 'Array argument "{0}" cannot be required.',
    'arrayOptionInvalidDefault'             => 'Array option "--{0}" must have an array default value or null.',
    'arrayOptionMustRequireValue'           => 'Array option "--{0}" must require a value.',
    'arrayOptionEmptyArrayDefault'          => 'Array option "--{0}" cannot have an empty array as the default value.',
    'argumentAfterArrayArgument'            => 'Argument "{0}" cannot be defined after array argument "{1}".',
    'duplicateArgument'                     => 'An argument with the name "{0}" is already defined.',
    'duplicateCommandName'                  => 'Warning: The "{0}" command is defined as both legacy ({1}) and modern ({2}). The legacy command will be executed. Please rename or remove one.',
    'duplicateOption'                       => 'An option with the name "--{0}" is already defined.',
    'duplicateShortcut'                     => 'Shortcut "-{0}" cannot be used for option "--{1}"; it is already assigned to option "--{2}".',
    'emptyCommandName'                      => 'Command name cannot be empty.',
    'emptyArgumentName'                     => 'Argument name cannot be empty.',
    'emptyOptionName'                       => 'Option name cannot be empty.',
    'emptyShortcutName'                     => 'Shortcut name cannot be empty.',
    'flagOptionPassedMultipleTimes'         => 'Option "--{0}" is passed multiple times.',
    'invalidCommandName'                    => 'Command name "{0}" is not valid.',
    'invalidArgumentName'                   => 'Argument name "{0}" is not valid.',
    'invalidOptionName'                     => 'Option name "--{0}" is not valid.',
    'invalidShortcutName'                   => 'Shortcut name "-{0}" is not valid.',
    'invalidShortcutNameLength'             => 'Shortcut name "-{0}" must be a single character.',
    'missingCommandAttribute'               => 'Command class "{0}" is missing the {1} attribute.',
    'missingRequiredArguments'              => 'Command "{0}" is missing the following required {1, plural, =1{argument} other{arguments}}: {2}.',
    'negatableOptionNegationExists'         => 'Negatable option "--{0}" cannot be defined because its negation "--no-{0}" already exists as an option.',
    'negatableOptionNoValue'                => 'Negatable option "--{0}" does not accept a value.',
    'negatableOptionMustNotAcceptValue'     => 'Negatable option "--{0}" cannot be defined to accept a value.',
    'negatableOptionCannotBeArray'          => 'Negatable option "--{0}" cannot be defined as an array.',
    'negatableOptionInvalidDefault'         => 'Negatable option "--{0}" must have a boolean default value.',
    'negatableOptionPassedMultipleTimes'    => 'Negatable option "--{0}" is passed multiple times.',
    'negatableOptionWithNegation'           => 'Option "--{0}" and its negation "--{1}" cannot be used together.',
    'negatedOptionNoValue'                  => 'Negated option "--{0}" does not accept a value.',
    'negatedOptionPassedMultipleTimes'      => 'Negated option "--{0}" is passed multiple times.',
    'noArgumentsExpected'                   => 'No arguments expected for "{0}" command. Received: "{1}".',
    'nonArrayArgumentWithArrayDefault'      => 'Argument "{0}" does not accept an array default value.',
    'nonArrayOptionWithArrayValue'          => 'Option "--{0}" does not accept an array value.',
    'optionClashesWithExistingNegation'     => 'Option "--{0}" clashes with the negation of negatable option "--{1}".',
    'optionNoValueAndNoDefault'             => 'Option "--{0}" does not accept a value and cannot have a default value.',
    'optionNotAcceptingValue'               => 'Option "--{0}" does not accept a value.',
    'optionalArgumentNoDefault'             => 'Argument "{0}" is optional and must have a default value.',
    'optionRequiresStringDefaultValue'      => 'Option "--{0}" requires a string default value.',
    'optionRequiresValue'                   => 'Option "--{0}" requires a value to be provided.',
    'requiredArgumentNoDefault'             => 'Argument "{0}" is required and must not have a default value.',
    'requiredArgumentAfterOptionalArgument' => 'Required argument "{0}" cannot be defined after optional argument "{1}".',
    'reservedArgumentName'                  => 'Argument name "extra_arguments" is reserved and cannot be used.',
    'reservedOptionName'                    => 'Option name "--extra_options" is reserved and cannot be used.',
    'tooManyArguments'                      => '{1, plural, =1{One unexpected argument was} other{Multiple unexpected arguments were}} provided to "{0}" command: "{2}".',
    'unknownOptions'                        => 'The following {0, plural, =1{option} other{options}} {0, plural, =1{is} other{are}} unknown in the "{1}" command: {2}.',
];
