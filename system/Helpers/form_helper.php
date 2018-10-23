<?php

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2018 British Columbia Institute of Technology
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
 * @package	CodeIgniter
 * @author	CodeIgniter Dev Team
 * @copyright	2014-2018 British Columbia Institute of Technology (https://bcit.ca/)
 * @license	https://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 * @since	Version 3.0.0
 * @filesource
 */
use Config\Services;

//--------------------------------------------------------------------

if ( ! function_exists('form_open'))
{

	/**
	 * Form Declaration
	 *
	 * Creates the opening portion of the form.
	 *
	 * @param    string $action     the URI segments of the form destination
	 * @param    array|string  $attributes a key/value pair of attributes, or string representation
	 * @param    array  $hidden     a key/value pair hidden data
	 *
	 * @return    string
	 */
	function form_open(string $action = '', $attributes = [], array $hidden = []): string
	{
		// If no action is provided then set to the current url
		if ( ! $action)
		{
			$action = current_url(true);
		} // If an action is not a full URL then turn it into one
		elseif (strpos($action, '://') === false)
		{
			$action = site_url($action);
		}

		$attributes = stringify_attributes($attributes);

		if (stripos($attributes, 'method=') === false)
		{
			$attributes .= ' method="post"';
		}
		if (stripos($attributes, 'accept-charset=') === false)
		{
			$config = config(\Config\App::class);
			$attributes .= ' accept-charset="' . strtolower($config->charset) . '"';
		}

		$form = '<form action="' . $action . '"' . $attributes . ">\n";

		// Add CSRF field if enabled, but leave it out for GET requests and requests to external websites
		$before = Services::filters()->getFilters()['before'];

		if ((in_array('csrf', $before) || array_key_exists('csrf', $before)) && strpos($action, base_url()) !== false && ! stripos($form, 'method="get"')
		)
		{
			$hidden[csrf_token()] = csrf_hash();
		}

		if (is_array($hidden))
		{
			foreach ($hidden as $name => $value)
			{
				$form .= '<input type="hidden" name="' . $name . '" value="' . esc($value,'html') . '" style="display: none;" />' . "\n";
			}
		}

		return $form;
	}

}

//--------------------------------------------------------------------

if ( ! function_exists('form_open_multipart'))
{

	/**
	 * Form Declaration - Multipart type
	 *
	 * Creates the opening portion of the form, but with "multipart/form-data".
	 *
	 * @param    string $action     The URI segments of the form destination
	 * @param    array|string  $attributes A key/value pair of attributes, or the same as a string
	 * @param    array  $hidden     A key/value pair hidden data
	 *
	 * @return    string
	 */
	function form_open_multipart(string $action = '', $attributes = [], array $hidden = []): string
	{
		if (is_string($attributes))
		{
			$attributes .= ' enctype="' . esc('multipart/form-data', 'attr') . '"';
		}
		else
		{
			$attributes['enctype'] = 'multipart/form-data';
		}

		return form_open($action, $attributes, $hidden);
	}

}

//--------------------------------------------------------------------

if ( ! function_exists('form_hidden'))
{

	/**
	 * Hidden Input Field
	 *
	 * Generates hidden fields. You can pass a simple key/value string or
	 * an associative array with multiple values.
	 *
	 * @param    string|array $name  Field name or associative array to create multiple fields
	 * @param    string|array $value Field value
	 * @param    bool         $recursing
	 *
	 * @return    string
	 */
	function form_hidden($name, $value = '', bool $recursing = false): string
	{
		static $form;

		if ($recursing === false)
		{
			$form = "\n";
		}

		if (is_array($name))
		{
			foreach ($name as $key => $val)
			{
				form_hidden($key, $val, true);
			}

			return $form;
		}

		if ( ! is_array($value))
		{
			$form .= '<input type="hidden" name="' . $name . '" value="' . esc($value,'html') . "\" />\n";
		}
		else
		{
			foreach ($value as $k => $v)
			{
				$k = is_int($k) ? '' : $k;
				form_hidden($name . '[' . $k . ']', $v, true);
			}
		}

		return $form;
	}

}

//--------------------------------------------------------------------

if ( ! function_exists('form_input'))
{

	/**
	 * Text Input Field. If 'type' is passed in the $type field, it will be
	 * used as the input type, for making 'email', 'phone', etc input fields.
	 *
	 * @param    mixed  $data
	 * @param    string $value
	 * @param    mixed  $extra
	 * @param   string  $type
	 *
	 * @return    string
	 */
	function form_input($data = '', string $value = '', $extra = '', string $type = 'text'): string
	{
		$defaults = [
			'type' => $type,
			'name' => is_array($data) ? '' : $data,
			'value' => $value,
		];

		return '<input ' . parse_form_attributes($data, $defaults) . stringify_attributes($extra) . " />\n";
	}

}

//--------------------------------------------------------------------

if ( ! function_exists('form_password'))
{

	/**
	 * Password Field
	 *
	 * Identical to the input function but adds the "password" type
	 *
	 * @param    mixed  $data
	 * @param    string $value
	 * @param    mixed  $extra
	 *
	 * @return    string
	 */
	function form_password($data = '', string $value = '', $extra = ''): string
	{
		is_array($data) || $data = ['name' => $data];
		$data['type'] = 'password';

		return form_input($data, $value, $extra);
	}

}

//--------------------------------------------------------------------

if ( ! function_exists('form_upload'))
{

	/**
	 * Upload Field
	 *
	 * Identical to the input function but adds the "file" type
	 *
	 * @param    mixed
	 * @param    string
	 * @param    mixed
	 *
	 * @return    string
	 */
	function form_upload($data = '', string $value = '', $extra = ''): string
	{
		$defaults = ['type' => 'file', 'name' => ''];
		is_array($data) || $data = ['name' => $data];
		$data['type'] = 'file';

		return '<input ' . parse_form_attributes($data, $defaults) . stringify_attributes($extra) . " />\n";
	}

}

//--------------------------------------------------------------------

if ( ! function_exists('form_textarea'))
{

	/**
	 * Textarea field
	 *
	 * @param    mixed  $data
	 * @param    string $value
	 * @param    mixed  $extra
	 *
	 * @return    string
	 */
	function form_textarea($data = '', string $value = '', $extra = ''): string
	{
		$defaults = [
			'name' => is_array($data) ? '' : $data,
			'cols' => '40',
			'rows' => '10',
		];
		if ( ! is_array($data) || ! isset($data['value']))
		{
			$val = $value;
		}
		else
		{
			$val = $data['value'];
			unset($data['value']); // textareas don't use the value attribute
		}

		return '<textarea ' . parse_form_attributes($data, $defaults) . stringify_attributes($extra) . '>'
				. htmlspecialchars($val)
				. "</textarea>\n";
	}

}

//--------------------------------------------------------------------

if ( ! function_exists('form_multiselect'))
{

	/**
	 * Multi-select menu
	 *
	 * @param    string
	 * @param    array
	 * @param    mixed
	 * @param    mixed
	 *
	 * @return    string
	 */
	function form_multiselect(string $name = '', array $options = [], array $selected = [], $extra = ''): string
	{
		$extra = stringify_attributes($extra);

		if (stripos($extra, 'multiple') === false)
		{
			$extra .= ' multiple="multiple"';
		}

		return form_dropdown($name, $options, $selected, $extra);
	}

}

//--------------------------------------------------------------------

if ( ! function_exists('form_dropdown'))
{

	/**
	 * Drop-down Menu
	 *
	 * @param    mixed $data
	 * @param    mixed $options
	 * @param    mixed $selected
	 * @param    mixed $extra
	 *
	 * @return    string
	 */
	function form_dropdown($data = '', $options = [], $selected = [], $extra = ''): string
	{
		$defaults = [];
		if (is_array($data))
		{
			if (isset($data['selected']))
			{
				$selected = $data['selected'];
				unset($data['selected']); // select tags don't have a selected attribute
			}
			if (isset($data['options']))
			{
				$options = $data['options'];
				unset($data['options']); // select tags don't use an options attribute
			}
		}
		else
		{
			$defaults = ['name' => $data];
		}

		is_array($selected) || $selected = [$selected];
		is_array($options) || $options = [$options];

		// If no selected state was submitted we will attempt to set it automatically
		if (empty($selected))
		{
			if (is_array($data))
			{
				if (isset($data['name'], $_POST[$data['name']]))
				{
					$selected = [$_POST[$data['name']]];
				}
			}
			elseif (isset($_POST[$data]))
			{
				$selected = [$_POST[$data]];
			}
		}

		$extra = stringify_attributes($extra);
		$multiple = (count($selected) > 1 && stripos($extra, 'multiple') === false) ? ' multiple="multiple"' : '';
		$form = '<select ' . rtrim(parse_form_attributes($data, $defaults)) . $extra . $multiple . ">\n";
		foreach ($options as $key => $val)
		{
			$key = (string) $key;
			if (is_array($val))
			{
				if (empty($val))
				{
					continue;
				}
				$form .= '<optgroup label="' . $key . "\">\n";
				foreach ($val as $optgroup_key => $optgroup_val)
				{
					$sel = in_array($optgroup_key, $selected) ? ' selected="selected"' : '';
					$form .= '<option value="' . htmlspecialchars($optgroup_key) . '"' . $sel . '>'
							. (string) $optgroup_val . "</option>\n";
				}
				$form .= "</optgroup>\n";
			}
			else
			{
				$form .= '<option value="' . htmlspecialchars($key) . '"'
						. (in_array($key, $selected) ? ' selected="selected"' : '') . '>'
						. (string) $val . "</option>\n";
			}
		}

		return $form . "</select>\n";
	}

}

//--------------------------------------------------------------------

if ( ! function_exists('form_checkbox'))
{

	/**
	 * Checkbox Field
	 *
	 * @param    mixed  $data
	 * @param    string $value
	 * @param    bool   $checked
	 * @param    mixed  $extra
	 *
	 * @return    string
	 */
	function form_checkbox($data = '', string $value = '', bool $checked = false, $extra = ''): string
	{
		$defaults = ['type' => 'checkbox', 'name' => ( ! is_array($data) ? $data : ''), 'value' => $value];

		if (is_array($data) && array_key_exists('checked', $data))
		{
			$checked = $data['checked'];
			if ($checked == false)
			{
				unset($data['checked']);
			}
			else
			{
				$data['checked'] = 'checked';
			}
		}

		if ($checked == true)
		{
			$defaults['checked'] = 'checked';
		}
		else
		{
			unset($defaults['checked']);
		}

		return '<input ' . parse_form_attributes($data, $defaults) . stringify_attributes($extra) . " />\n";
	}

}

//--------------------------------------------------------------------

if ( ! function_exists('form_radio'))
{

	/**
	 * Radio Button
	 *
	 * @param    mixed  $data
	 * @param    string $value
	 * @param    bool   $checked
	 * @param    mixed  $extra
	 *
	 * @return    string
	 */
	function form_radio($data = '', string $value = '', bool $checked = false, $extra = ''): string
	{
		is_array($data) || $data = ['name' => $data];
		$data['type'] = 'radio';

		return form_checkbox($data, $value, $checked, $extra);
	}

}

//--------------------------------------------------------------------

if ( ! function_exists('form_submit'))
{

	/**
	 * Submit Button
	 *
	 * @param    mixed  $data
	 * @param    string $value
	 * @param    mixed  $extra
	 *
	 * @return    string
	 */
	function form_submit($data = '', string $value = '', $extra = ''): string
	{
		$defaults = [
			'type' => 'submit',
			'name' => is_array($data) ? '' : $data,
			'value' => $value,
		];

		return '<input ' . parse_form_attributes($data, $defaults) . stringify_attributes($extra) . " />\n";
	}

}

//--------------------------------------------------------------------

if ( ! function_exists('form_reset'))
{

	/**
	 * Reset Button
	 *
	 * @param    mixed  $data
	 * @param    string $value
	 * @param    mixed  $extra
	 *
	 * @return    string
	 */
	function form_reset($data = '', string $value = '', $extra = ''): string
	{
		$defaults = [
			'type' => 'reset',
			'name' => is_array($data) ? '' : $data,
			'value' => $value,
		];

		return '<input ' . parse_form_attributes($data, $defaults) . stringify_attributes($extra) . " />\n";
	}

}

//--------------------------------------------------------------------

if ( ! function_exists('form_button'))
{

	/**
	 * Form Button
	 *
	 * @param    mixed  $data
	 * @param    string $content
	 * @param    mixed  $extra
	 *
	 * @return    string
	 */
	function form_button($data = '', string $content = '', $extra = ''): string
	{
		$defaults = [
			'name' => is_array($data) ? '' : $data,
			'type' => 'button',
		];

		if (is_array($data) && isset($data['content']))
		{
			$content = $data['content'];
			unset($data['content']); // content is not an attribute
		}

		return '<button ' . parse_form_attributes($data, $defaults) . stringify_attributes($extra) . '>'
				. $content
				. "</button>\n";
	}

}

//--------------------------------------------------------------------

if ( ! function_exists('form_label'))
{

	/**
	 * Form Label Tag
	 *
	 * @param    string $label_text The text to appear onscreen
	 * @param    string $id         The id the label applies to
	 * @param    array  $attributes Additional attributes
	 *
	 * @return    string
	 */
	function form_label(string $label_text = '', string $id = '', array $attributes = []): string
	{
		$label = '<label';

		if ($id !== '')
		{
			$label .= ' for="' . $id . '"';
		}

		if (is_array($attributes) && $attributes)
		{
			foreach ($attributes as $key => $val)
			{
				$label .= ' ' . $key . '="' . $val . '"';
			}
		}

		return $label . '>' . $label_text . '</label>';
	}

}

//--------------------------------------------------------------------

if ( ! function_exists('form_datalist'))
{

	/**
	 * Datalist
	 *
	 * The <datalist> element specifies a list of pre-defined options for an <input> element.
	 * Users will see a drop-down list of pre-defined options as they input data.
	 * The list attribute of the <input> element, must refer to the id attribute of the <datalist> element.
	 *
	 * @param string $name
	 * @param string $value
	 * @param array  $options
	 *
	 * @return string
	 */
	function form_datalist($name, $value, $options)
	{
		$data = [
			'type' => 'text',
			'name' => $name,
			'list' => $name . '_list',
			'value' => $value,
		];

		$out = form_input($data) . "\n";
		$out .= "<datalist id='" . $name . '_list' . "'>";

		foreach ($options as $option)
		{
			$out .= "<option value='$option'>" . "\n";
		}

		$out .= "</datalist>" . "\n";

		return $out;
	}

}

//--------------------------------------------------------------------

if ( ! function_exists('form_fieldset'))
{

	/**
	 * Fieldset Tag
	 *
	 * Used to produce <fieldset><legend>text</legend>.  To close fieldset
	 * use form_fieldset_close()
	 *
	 * @param    string $legend_text The legend text
	 * @param    array  $attributes  Additional attributes
	 *
	 * @return    string
	 */
	function form_fieldset(string $legend_text = '', array $attributes = []): string
	{
		$fieldset = '<fieldset' . stringify_attributes($attributes) . ">\n";

		if ($legend_text !== '')
		{
			return $fieldset . '<legend>' . $legend_text . "</legend>\n";
		}

		return $fieldset;
	}

}

//--------------------------------------------------------------------

if ( ! function_exists('form_fieldset_close'))
{

	/**
	 * Fieldset Close Tag
	 *
	 * @param    string $extra
	 *
	 * @return    string
	 */
	function form_fieldset_close(string $extra = ''): string
	{
		return '</fieldset>' . $extra;
	}

}

//--------------------------------------------------------------------

if ( ! function_exists('form_close'))
{

	/**
	 * Form Close Tag
	 *
	 * @param    string $extra
	 *
	 * @return    string
	 */
	function form_close(string $extra = ''): string
	{
		return '</form>' . $extra;
	}

}

//--------------------------------------------------------------------

if ( ! function_exists('set_value'))
{

	/**
	 * Form Value
	 *
	 * Grabs a value from the POST array for the specified field so you can
	 * re-populate an input field or textarea
	 *
	 * @param    string $field       Field name
	 * @param    string $default     Default value
	 * @param    bool   $html_escape Whether to escape HTML special characters or not
	 *
	 * @return    string
	 */
	function set_value(string $field, string $default = '', bool $html_escape = true): string
	{
		$request = Services::request();

		// Try any old input data we may have first
		$value = $request->getOldInput($field);

		if ($value === null)
		{
			$value = $request->getPost($field) ?? $default;
		}

		return ($html_escape) ? esc($value, 'html') : $value;
	}

}

//--------------------------------------------------------------------

if ( ! function_exists('set_select'))
{

	/**
	 * Set Select
	 *
	 * Let's you set the selected value of a <select> menu via data in the POST array.
	 * If Form Validation is active it retrieves the info from the validation class
	 *
	 * @param    string $field
	 * @param    string $value
	 * @param    bool   $default
	 *
	 * @return    string
	 */
	function set_select(string $field, string $value = '', bool $default = false): string
	{
		$request = Services::request();

		// Try any old input data we may have first
		$input = $request->getOldInput($field);

		if ($input === null)
		{
			$input = $request->getPost($field);
		}

		if ($input === null)
		{
			return ($default === true) ? ' selected="selected"' : '';
		}

		if (is_array($input))
		{
			// Note: in_array('', array(0)) returns TRUE, do not use it
			foreach ($input as &$v)
			{
				if ($value === $v)
				{
					return ' selected="selected"';
				}
			}

			return '';
		}

		return ($input === $value) ? ' selected="selected"' : '';
	}

}

//--------------------------------------------------------------------

if ( ! function_exists('set_checkbox'))
{

	/**
	 * Set Checkbox
	 *
	 * Let's you set the selected value of a checkbox via the value in the POST array.
	 * If Form Validation is active it retrieves the info from the validation class
	 *
	 * @param    string $field
	 * @param    string $value
	 * @param    bool   $default
	 *
	 * @return    string
	 */
	function set_checkbox(string $field, string $value = '', bool $default = false): string
	{
		$request = Services::request();

		// Try any old input data we may have first
		$input = $request->getOldInput($field);

		if ($input === null)
		{
			$input = $request->getPost($field);
		}

		if (is_array($input))
		{
			// Note: in_array('', array(0)) returns TRUE, do not use it
			foreach ($input as &$v)
			{
				if ($value === $v)
				{
					return ' checked="checked"';
				}
			}

			return '';
		}

		// Unchecked checkbox and radio inputs are not even submitted by browsers ...
		if ( ! empty($request->getPost()) || ! empty(old($field)))
		{
			return ($input === $value) ? ' checked="checked"' : '';
		}

		return ($default === true) ? ' checked="checked"' : '';
	}

}

//--------------------------------------------------------------------

if ( ! function_exists('set_radio'))
{

	/**
	 * Set Radio
	 *
	 * Let's you set the selected value of a radio field via info in the POST array.
	 * If Form Validation is active it retrieves the info from the validation class
	 *
	 * @param    string $field
	 * @param    string $value
	 * @param    bool   $default
	 *
	 * @return    string
	 */
	function set_radio(string $field, string $value = '', bool $default = false): string
	{
		$request = Services::request();

		// Try any old input data we may have first
		$input = $request->getOldInput($field);
		if ($input === null)
		{
			$input = $request->getPost($field) ?? $default;
		}

		if (is_array($input))
		{
			// Note: in_array('', array(0)) returns TRUE, do not use it
			foreach ($input as &$v)
			{
				if ($value === $v)
				{
					return ' checked="checked"';
				}
			}

			return '';
		}

		// Unchecked checkbox and radio inputs are not even submitted by browsers ...
		$result = '';
		if (!empty($input = $request->getPost($field)) || !empty($input = old($field)))
		{
			$result = ($input === $value) ? ' checked="checked"' : '';
		}

		if (empty($result))
			$result = ($default === true) ? ' checked="checked"' : '';
		return $result;
	}

}

//--------------------------------------------------------------------

if ( ! function_exists('parse_form_attributes'))
{

	/**
	 * Parse the form attributes
	 *
	 * Helper function used by some of the form helpers
	 *
	 * @param    array $attributes List of attributes
	 * @param    array $default    Default values
	 *
	 * @return    string
	 */
	function parse_form_attributes($attributes, $default): string
	{
		if (is_array($attributes))
		{
			foreach ($default as $key => $val)
			{
				if (isset($attributes[$key]))
				{
					$default[$key] = $attributes[$key];
					unset($attributes[$key]);
				}
			}
			if ( ! empty($attributes))
			{
				$default = array_merge($default, $attributes);
			}
		}

		$att = '';

		foreach ($default as $key => $val)
		{
			if ($key === 'value')
			{
				$val = esc($val, 'html');
			}
			elseif ($key === 'name' && ! strlen($default['name']))
			{
				continue;
			}
			$att .= $key . '="' . $val . '" ';
		}

		return $att;
	}

	//--------------------------------------------------------------------
}
