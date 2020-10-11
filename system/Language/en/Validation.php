<?php

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2019 British Columbia Institute of Technology
 * Copyright (c) 2019-2020 CodeIgniter Foundation
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
 * @package    CodeIgniter
 * @author     CodeIgniter Dev Team
 * @copyright  2019-2020 CodeIgniter Foundation
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link       https://codeigniter.com
 * @since      Version 4.0.0
 * @filesource
 */

/*
 * Validation language strings
 *
 * @codeCoverageIgnore
 */

return [
	// Core Messages
	'noRuleSets'      => 'No rulesets specified in Validation configuration.',
	'ruleNotFound'    => '{0} is not a valid rule.',
	'groupNotFound'   => '{0} is not a validation rules group.',
	'groupNotArray'   => '{0} rule group must be an array.',
	'invalidTemplate' => '{0} is not a valid Validation template.',

	// Rule Messages
	'alpha'                 => 'The {field} field may only contain alphabetical characters.',
	'alpha_dash'            => 'The {field} field may only contain alphanumeric, underscore, and dash characters.',
	'alpha_numeric'         => 'The {field} field may only contain alphanumeric characters.',
	'alpha_numeric_punct'   => 'The {field} field may contain only alphanumeric characters, spaces, and  ~ ! # $ % & * - _ + = | : . characters.',
	'alpha_numeric_space'   => 'The {field} field may only contain alphanumeric and space characters.',
	'alpha_space'           => 'The {field} field may only contain alphabetical characters and spaces.',
	'decimal'               => 'The {field} field must contain a decimal number.',
	'differs'               => 'The {field} field must differ from the {param} field.',
	'equals'                => 'The {field} field must be exactly: {param}.',
	'exact_length'          => 'The {field} field must be exactly {param} characters in length.',
	'greater_than'          => 'The {field} field must contain a number greater than {param}.',
	'greater_than_equal_to' => 'The {field} field must contain a number greater than or equal to {param}.',
	'hex'                   => 'The {field} field may only contain hexidecimal characters.',
	'in_list'               => 'The {field} field must be one of: {param}.',
	'integer'               => 'The {field} field must contain an integer.',
	'is_natural'            => 'The {field} field must only contain digits.',
	'is_natural_no_zero'    => 'The {field} field must only contain digits and must be greater than zero.',
	'is_not_unique'         => 'The {field} field must contain a previously existing value in the database.',
	'is_unique'             => 'The {field} field must contain a unique value.',
	'less_than'             => 'The {field} field must contain a number less than {param}.',
	'less_than_equal_to'    => 'The {field} field must contain a number less than or equal to {param}.',
	'matches'               => 'The {field} field does not match the {param} field.',
	'max_length'            => 'The {field} field cannot exceed {param} characters in length.',
	'min_length'            => 'The {field} field must be at least {param} characters in length.',
	'not_equals'            => 'The {field} field cannot be: {param}.',
	'not_in_list'           => 'The {field} field must not be one of: {param}.',
	'numeric'               => 'The {field} field must contain only numbers.',
	'regex_match'           => 'The {field} field is not in the correct format.',
	'required'              => 'The {field} field is required.',
	'required_with'         => 'The {field} field is required when {param} is present.',
	'required_without'      => 'The {field} field is required when {param} is not present.',
	'string'                => 'The {field} field must be a valid string.',
	'timezone'              => 'The {field} field must be a valid timezone.',
	'valid_base64'          => 'The {field} field must be a valid base64 string.',
	'valid_email'           => 'The {field} field must contain a valid email address.',
	'valid_emails'          => 'The {field} field must contain all valid email addresses.',
	'valid_ip'              => 'The {field} field must contain a valid IP.',
	'valid_url'             => 'The {field} field must contain a valid URL.',
	'valid_date'            => 'The {field} field must contain a valid date.',

	// Credit Cards
	'valid_cc_num' => '{field} does not appear to be a valid credit card number.',

	// Files
	'uploaded' => '{field} is not a valid uploaded file.',
	'max_size' => '{field} is too large of a file.',
	'is_image' => '{field} is not a valid, uploaded image file.',
	'mime_in'  => '{field} does not have a valid mime type.',
	'ext_in'   => '{field} does not have a valid file extension.',
	'max_dims' => '{field} is either not an image, or it is too wide or tall.',
];
