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
                    '='  => 'align_single_space_minimal',
                    '=>' => 'align_single_space_minimal',
                    '||' => 'align_single_space_minimal',
                    '.=' => 'align_single_space_minimal',
                ],
            ],
            'blank_line_after_namespace'   => true,
            'blank_line_after_opening_tag' => true,
            'blank_line_before_statement'  => [
                'statements' => [
                    'case',
                    'continue',
                    'declare',
                    'default',
                    'do',
                    'exit',
                    'for',
                    'foreach',
                    'goto',
                    'return',
                    'switch',
                    'throw',
                    'try',
                    'while',
                    'yield',
                    'yield_from',
                ],
            ],
            'braces' => [
                'allow_single_line_anonymous_class_with_empty_body' => true,
                'allow_single_line_closure'                         => true,
                'position_after_anonymous_constructs'               => 'same',
                'position_after_control_structures'                 => 'same',
                'position_after_functions_and_oop_constructs'       => 'next',
            ],
            'cast_spaces'                 => ['space' => 'single'],
            'class_attributes_separation' => [
                'elements' => [
                    // 'const' => 'one_if_phpdoc', // @todo Enable in php-cs-fixer v3.1
                    // 'property' => 'one_if_phpdoc', // @todo Enable in php-cs-fixer v3.1
                    'method' => 'one',
                ],
            ],
            'class_definition' => [
                'multi_line_extends_each_single_line' => true,
                'single_item_single_line'             => true,
                'single_line'                         => true,
            ],
            'class_keyword_remove'       => false,
            'clean_namespace'            => true,
            'combine_consecutive_issets' => true,
            'combine_consecutive_unsets' => true,
            'combine_nested_dirname'     => true,
            'comment_to_phpdoc'          => [
                'ignored_tags' => [
                    'todo',
                    'codeCoverageIgnore',
                    'codeCoverageIgnoreStart',
                    'codeCoverageIgnoreEnd',
                    'phpstan-ignore-line',
                    'phpstan-ignore-next-line',
                ],
            ],
            'compact_nullable_typehint'              => true,
            'concat_space'                           => ['spacing' => 'one'],
            'constant_case'                          => ['case' => 'lower'],
            'function_to_constant'                   => true,
            'heredoc_indentation'                    => ['indentation' => 'start_plus_one'],
            'heredoc_to_nowdoc'                      => true,
            'increment_style'                        => ['style' => 'post'],
            'indentation_type'                       => true,
            'lambda_not_used_import'                 => true,
            'line_ending'                            => true,
            'linebreak_after_opening_tag'            => true,
            'list_syntax'                            => ['syntax' => 'short'],
            'logical_operators'                      => true,
            'lowercase_cast'                         => true,
            'lowercase_keywords'                     => true,
            'lowercase_static_reference'             => true,
            'magic_constant_casing'                  => true,
            'magic_method_casing'                    => true,
            'mb_str_functions'                       => false,
            'modernize_types_casting'                => true,
            'multiline_comment_opening_closing'      => true,
            'multiline_whitespace_before_semicolons' => ['strategy' => 'no_multi_line'],
            'no_alias_functions'                     => ['sets' => ['@all']],
            'no_short_bool_cast'                     => true,
            'no_trailing_comma_in_singleline_array'  => true,
            'no_unset_cast'                          => true,
            'no_whitespace_before_comma_in_array'    => ['after_heredoc' => true],
            'not_operator_with_space'                => false,
            'not_operator_with_successor_space'      => true,
            'normalize_index_brace'                  => true,
            'ordered_imports'                        => ['sort_algorithm' => 'alpha'],
            'phpdoc_align'                           => true,
            'phpdoc_indent'                          => true,
            'phpdoc_inline_tag_normalizer'           => [
                'tags' => [
                    'example',
                    'id',
                    'internal',
                    'inheritdoc',
                    'inheritdocs',
                    'link',
                    'source',
                    'toc',
                    'tutorial',
                ],
            ],
            'phpdoc_line_span' => [
                'const'    => 'multi',
                'method'   => 'multi',
                'property' => 'multi',
            ],
            'phpdoc_no_access'    => true,
            'phpdoc_no_alias_tag' => [
                'replacements' => [
                    'property-read'  => 'property',
                    'property-write' => 'property',
                    'type'           => 'var',
                    'link'           => 'see',
                ],
            ],
            'phpdoc_no_empty_return'       => false,
            'phpdoc_no_package'            => true,
            'phpdoc_no_useless_inheritdoc' => true,
            'phpdoc_order'                 => true,
            'phpdoc_order_by_value'        => [
                'annotations' => [
                    'author',
                    'covers',
                    'coversNothing',
                    'dataProvider',
                    'depends',
                    'group',
                    'internal',
                    'method',
                    'property',
                    'property-read',
                    'property-write',
                    'requires',
                    'throws',
                    'uses',
                ],
            ],
            'phpdoc_scalar' => [
                'types' => [
                    'boolean',
                    'callback',
                    'double',
                    'integer',
                    'real',
                    'str',
                ],
            ],
            'phpdoc_separation'                             => true,
            'phpdoc_trim'                                   => true,
            'phpdoc_trim_consecutive_blank_line_separation' => true,
            'phpdoc_types'                                  => ['groups' => ['simple', 'alias', 'meta']],
            'phpdoc_types_order'                            => [
                'null_adjustment' => 'always_last',
                'sort_algorithm'  => 'alpha',
            ],
            'set_type_to_cast'               => true,
            'short_scalar_cast'              => true,
            'standardize_increment'          => true,
            'static_lambda'                  => true,
            'switch_case_semicolon_to_colon' => true,
            'switch_case_space'              => true,
            'switch_continue_to_break'       => true,
            'ternary_operator_spaces'        => true,
            'ternary_to_elvis_operator'      => true,
            'ternary_to_null_coalescing'     => true,
            'trailing_comma_in_multiline'    => [
                'after_heredoc' => true,
                'elements'      => ['arrays'],
            ],
            'trim_array_spaces'               => true,
            'whitespace_after_comma_in_array' => true,
            'unary_operator_spaces'           => true,
            'visibility_required'             => ['elements' => ['const', 'method', 'property']],
            'yoda_style'                      => [
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
