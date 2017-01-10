<?php

$finder = Symfony\CS\Finder\DefaultFinder::create()
		->exclude('ThirdParty')
		->in('system');

return Symfony\CS\Config\Config::create()
		->level(Symfony\CS\FixerInterface::PSR1_LEVEL)
		->fixers(array(
			'class_definition',
			'eof_ending',
			'function_call_space',
			'linefeed',
			'lowercase_constants',
			'lowercase_keywords',
			'method_argument_space',
			'no_trailing_whitespace_in_comment',
			'parenthesis',
			'php_closing_tag',
			'trailing_spaces',
			'visibility',
			'array_element_no_space_before_comma',
			'array_element_white_space_after_comma',
			'concat_without_spaces',
			'include',
			'operators_spaces',
			'spaces_before_semicolon',
			'ternary_spaces',
			'unary_operators_spaces',
			'spaces_cast',
			'whitespacy_lines',
			'align_double_arrow',
			'align_equals',
			'logical_not_operators_with_spaces',
			'logical_not_operators_with_successor_space',
			'multiline_spaces_before_semicolon',
		))
		->finder($finder);
