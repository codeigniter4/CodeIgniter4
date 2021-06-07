<?php

declare(strict_types=1);

namespace Utils\PhpCsFixer;

use Nexus\CsConfig\Ruleset\AbstractRuleset;

/**
 * Defines the ruleset used for the CodeIgniter4 organization.
 *
 * @internal
 */
final class CodeIgniter4 extends AbstractRuleset
{
    public function __construct()
    {
        $this->name = 'CodeIgniter4 Revised Coding Standards';

        $this->rules = [
            'align_multiline_comment' => ['comment_type' => 'phpdocs_only'],
            'array_indentation'       => true,
            'array_push'              => true, // risky
            'array_syntax'            => ['syntax' => 'short'],
            'backtick_to_shell_exec'  => true,
            'binary_operator_spaces'  => [
                'default'   => 'single_space',
                'operators' => [
                    '='  => 'align_single_space',
                    '=>' => 'align_single_space',
                    '||' => 'align_single_space',
                    '.=' => 'align_single_space',
                ],
            ],
            'blank_line_after_namespace'   => true,
            'blank_line_after_opening_tag' => true,
            'function_to_constant'         => true,
            'indentation_type'             => true,
            'line_ending'                  => true,
            'no_alias_functions'           => [
                'sets' => ['@all'],
            ],
            'static_lambda'                => true,
            'ternary_to_null_coalescing'   => true,
            'yoda_style'                   => [
                'equal'                => false,
                'identical'            => null,
                'less_and_greater'     => false,
                'always_move_variable' => false,
            ],
        ];

        $this->requiredPHPVersion = 70300;

        $this->autoActivateIsRiskyAllowed = true;
    }
}
