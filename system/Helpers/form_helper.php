<?php

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2016, British Columbia Institute of Technology
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
 * @author	EllisLab Dev Team
 * @copyright	Copyright (c) 2008 - 2014, EllisLab, Inc. (https://ellislab.com/)
 * @copyright	Copyright (c) 2014 - 2016, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 * @since	Version 1.0.0
 * @filesource
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * CodeIgniter Form Helpers
 *
 * @package		CodeIgniter
 * @subpackage	Helpers
 * @category	Helpers
 * @author		EllisLab Dev Team
 * @link		https://codeigniter.com/user_guide/helpers/form_helper.html
 */

// -------------------------------------------------------------------------------

if ( ! function_exists('form_open'))
{
    /**
     * Form Declaration
     *
     * Creates the opening portion of the form.
     *
     * @param	string	the URI segments of the form destination
     * @param	array	a key/value pair of attributes
     * @param	array	a key/value pair hidden data
     * @return	string
     */
    function form_open(string $action = '', array $attributes = [], array $hidden = []): string
    {
        // If no action is provided then set to the current url
        if ( ! $action)
        {
            $action = current_url(true);
        }
        // If an action is not a full URL then turn it into one
        elseif (strpos($action, '://') === false)
        {
            $action = site_url($action);
        }
        
        $attributes = _attributes_to_string($attributes);
        if (stripos($attributes, 'method=') === false)
        {
            $attributes .= ' method="post"';
        }
        if (stripos($attributes, 'accept-charset=') === false)
        {
            $attributes .= ' accept-charset="'.strtolower(config_item('charset')).'"';
        }
        
        $form = '<form action="'.$action.'"'.$attributes.">\n";
        
        // Add CSRF field if enabled, but leave it out for GET requests and requests to external websites
        $getCsrf = new \Config\Filters();
        $before = $getCsrf->globals->before;
        
        if ((in_array('csrf', $before) || array_key_exists('csrf', $before)) &&
                strpos($action, base_url()) !== false && ! stripos($form, 'method="get"'))
        {
            $security = \Config\Services::security();
            $hidden[$security->getCSRFTokenName()] = $security->getCSRFHash();
        }
        if (is_array($hidden))
        {
            foreach ($hidden as $name => $value)
            {
                $form .= '<input type="hidden" name="'.$name.'" value="'.esc($value, 'html').'" style="display:none;" />'."\n";
            }
        }
        return $form;
    }
}

// -----------------------------------------------------------------------

if ( ! function_exists('form_open_multipart'))
{  
    /**
     * Form Declaration - Multipart type
     *
     * Creates the opening portion of the form, but with "multipart/form-data".
     *
     * @param	string	the URI segments of the form destination
     * @param	array	a key/value pair of attributes
     * @param	array	a key/value pair hidden data
     * @return	string
     */
    function form_open_multipart(string $action = '', array $attributes = [], array $hidden = []): string
    {
        if (is_string($attributes))
        {
            $attributes .= ' enctype="multipart/form-data"';
        }
        else
        {
            $attributes['enctype'] = 'multipart/form-data';
        }
        
        return form_open($action, $attributes, $hidden);
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('form_hidden'))
{
    /**
     * Hidden Input Field
     *
     * Generates hidden fields. You can pass a simple key/value string or
     * an associative array with multiple values.
     *
     * @param	mixed	$name		Field name
     * @param	string	$value		Field value
     * @param	bool	$recursing
     * @return	string
     */
    function form_hidden($name, string $value = '', bool $recursing = false): string
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
            $form .= '<input type="hidden" name="'.$name.'" value="'.html_escape($value)."\" />\n";
        }
        else
        {
            foreach ($value as $k => $v)
            {
                $k = is_int($k) ? '' : $k;
                form_hidden($name.'['.$k.']', $v, true);
            }
        }
        
        return $form;
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('form_input'))
{
    /**
     * Text Input Field
     *
     * @param	mixed
     * @param	string
     * @param	mixed
     * @return	string
     */
    function form_input($data = '', string $value = '', $extra = ''): string
    {
        $defaults = [
                'type' => 'text',
                'name' => is_array($data) ? '' : $data,
                'value' => $value
        ];
        
        return '<input '._parse_form_attributes($data, $defaults)._attributes_to_string($extra)." />\n";
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('form_password'))
{
    /**
     * Password Field
     *
     * Identical to the input function but adds the "password" type
     *
     * @param	mixed
     * @param	string
     * @param	mixed
     * @return	string
     */
    function form_password($data = '', string $value = '', $extra = ''): string
    {
        is_array($data) OR $data = array('name' => $data);
        $data['type'] = 'password';
        
        return form_input($data, $value, $extra);
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('form_upload'))
{
    /**
     * Upload Field
     *
     * Identical to the input function but adds the "file" type
     *
     * @param	mixed
     * @param	string
     * @param	mixed
     * @return	string
     */
    function form_upload($data = '', string $value = '', $extra = ''): string
    {
        $defaults = ['type' => 'file', 'name' => ''];
        is_array($data) OR $data = ['name' => $data];
        $data['type'] = 'file';
        
        return '<input '._parse_form_attributes($data, $defaults)._attributes_to_string($extra)." />\n";
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('form_textarea'))
{
    /**
     * Textarea field
     *
     * @param	mixed	$data
     * @param	string	$value
     * @param	mixed	$extra
     * @return	string
     */
    function form_textarea($data = '', string $value = '', $extra = ''): string
    {
        $defaults = [
                'name' => is_array($data) ? '' : $data,
                'cols' => '40',
                'rows' => '10'
        ];
        if ( ! is_array($data) OR ! isset($data['value']))
        {
            $val = $value;
        }
        else
        {
            $val = $data['value'];
            unset($data['value']); // textareas don't use the value attribute
        }
        
        return '<textarea '._parse_form_attributes($data, $defaults)._attributes_to_string($extra).'>'
                .htmlspecialchars($val)
                ."</textarea>\n";
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('form_multiselect'))
{
    /**
     * Multi-select menu
     *
     * @param	string
     * @param	array
     * @param	mixed
     * @param	mixed
     * @return	string
     */
    function form_multiselect(string $name = '', array $options = [], array $selected = [], $extra = ''): string
    {
        $extra = _attributes_to_string($extra);
        
        if (stripos($extra, 'multiple') === false)
        {
            $extra .= ' multiple="multiple"';
        }
        
        return form_dropdown($name, $options, $selected, $extra);
    }
}

// --------------------------------------------------------------------

if ( ! function_exists('form_dropdown'))
{
    /**
     * Drop-down Menu
     *
     * @param	mixed	$data
     * @param	mixed	$options
     * @param	mixed	$selected
     * @param	mixed	$extra
     * @return	string
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
        
        is_array($selected) OR $selected = [$selected];
        is_array($options) OR $options = [$options];
        
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
        
        $extra = _attributes_to_string($extra);
        $multiple = (count($selected) > 1 && stripos($extra, 'multiple') === false) ? ' multiple="multiple"' : '';
        $form = '<select '.rtrim(_parse_form_attributes($data, $defaults)).$extra.$multiple.">\n";
        foreach ($options as $key => $val)
        {
            $key = (string) $key;
            if (is_array($val))
            {
                if (empty($val))
                {
                    continue;
                }
                $form .= '<optgroup label="'.$key."\">\n";
                foreach ($val as $optgroup_key => $optgroup_val)
                {
                    $sel = in_array($optgroup_key, $selected) ? ' selected="selected"' : '';
                    $form .= '<option value="'.htmlspecialchars($optgroup_key).'"'.$sel.'>'
                            .(string) $optgroup_val."</option>\n";
                }
                $form .= "</optgroup>\n";
            }
            else
            {
                $form .= '<option value="'.htmlspecialchars($key).'"'
                        .(in_array($key, $selected) ? ' selected="selected"' : '').'>'
                                .(string) $val."</option>\n";
            }
        }
        
        return $form."</select>\n";
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('form_checkbox'))
{
    /**
     * Checkbox Field
     *
     * @param	mixed
     * @param	string
     * @param	bool
     * @param	mixed
     * @return	string
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
        
        return '<input '._parse_form_attributes($data, $defaults)._attributes_to_string($extra)." />\n";
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('form_radio'))
{
    /**
     * Radio Button
     *
     * @param	mixed
     * @param	string
     * @param	bool
     * @param	mixed
     * @return	string
     */
    function form_radio($data = '', string $value = '', bool $checked = false, $extra = ''): string
    {
        is_array($data) OR $data = ['name' => $data];
        $data['type'] = 'radio';
        
        return form_checkbox($data, $value, $checked, $extra);
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('form_submit'))
{
    /**
     * Submit Button
     *
     * @param	mixed
     * @param	string
     * @param	mixed
     * @return	string
     */
    function form_submit($data = '', string $value = '', $extra = ''): string
    {
        $defaults = [
                'type' => 'submit',
                'name' => is_array($data) ? '' : $data,
                'value' => $value
        ];
        
        return '<input '._parse_form_attributes($data, $defaults)._attributes_to_string($extra)." />\n";
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('form_reset'))
{
    /**
     * Reset Button
     *
     * @param	mixed
     * @param	string
     * @param	mixed
     * @return	string
     */
    function form_reset($data = '', string $value = '', $extra = ''): string
    {
        $defaults = [
                'type' => 'reset',
                'name' => is_array($data) ? '' : $data,
                'value' => $value
        ];
        
        return '<input '._parse_form_attributes($data, $defaults)._attributes_to_string($extra)." />\n";
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('form_button'))
{
    /**
     * Form Button
     *
     * @param	mixed
     * @param	string
     * @param	mixed
     * @return	string
     */
    function form_button($data = '', string $content = '', $extra = ''): string
    {
        $defaults = [
                'name' => is_array($data) ? '' : $data,
                'type' => 'button'
        ];
        
        if (is_array($data) && isset($data['content']))
        {
            $content = $data['content'];
            unset($data['content']); // content is not an attribute
        }
        
        return '<button '._parse_form_attributes($data, $defaults)._attributes_to_string($extra).'>'
                .$content
                ."</button>\n";
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('form_label'))
{
    /**
     * Form Label Tag
     *
     * @param	string	The text to appear onscreen
     * @param	string	The id the label applies to
     * @param	array	Additional attributes
     * @return	string
     */
    function form_label(string $label_text = '', string $id = '', array $attributes = []): string
    {
        $label = '<label';
        
        if ($id !== '')
        {
            $label .= ' for="'.$id.'"';
        }
        
        if (is_array($attributes) && count($attributes) > 0)
        {
            foreach ($attributes as $key => $val)
            {
                $label .= ' '.$key.'="'.$val.'"';
            }
        }
        
        return $label.'>'.$label_text.'</label>';
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('form_datalist'))
{
    /**
     * Datalist
     * 
     * The <datalist> element specifies a list of pre-defined options for an <input> element.
     * Users will see a drop-down list of pre-defined options as they input data.
     * The list attribute of the <input> element, must refer to the id attribute of the <datalist> element.
     */
    function form_datalist($name, $value, $options)
    {
        $data = [
             'type' => 'text',
             'name' => $name,
             'list' => $name.'_list',
             'value' => $value
        ];
        
        $out = form_input($data)."\n";
        $out .= "<datalist id='".$name.'_list'."'>";
        
        foreach ($options as $option)
        {
            $out .= "<option value='$option'>"."\n";
        }
        
        $out .= "</datalist>"."\n";
        
        return $out;
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('form_keygen'))
{
    function form_keygen()
    {
        /* The purpose of the <keygen> element is to provide a secure way to authenticate users.
         The <keygen> element specifies a key-pair generator field in a form.
         When the form is submitted, two keys are generated, one private and one public.
         The private key is stored locally, and the public key is sent to the server.
         The public key could be used to generate a client certificate to authenticate the user in the future. */
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('form_output'))
{
    function form_output()
    {
        /* The <output> element represents the result of a calculation (like one performed by a script). */
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('form_fieldset'))
{
    /**
     * Fieldset Tag
     *
     * Used to produce <fieldset><legend>text</legend>.  To close fieldset
     * use form_fieldset_close()
     *
     * @param	string	The legend text
     * @param	array	Additional attributes
     * @return	string
     */
    function form_fieldset(string $legend_text = '', array $attributes = []): string
    {
        $fieldset = '<fieldset'._attributes_to_string($attributes).">\n";
        
        if ($legend_text !== '')
        {
            return $fieldset.'<legend>'.$legend_text."</legend>\n";
        }
        
        return $fieldset;
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('form_fieldset_close'))
{
    /**
     * Fieldset Close Tag
     *
     * @param	string
     * @return	string
     */
    function form_fieldset_close(string $extra = ''): string
    {
        return '</fieldset>'.$extra;
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('form_close'))
{
    /**
     * Form Close Tag
     *
     * @param	string
     * @return	string
     */
    function form_close(string $extra = ''): string
    {
        return '</form>'.$extra;
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('set_value'))
{
    /**
     * Form Value
     *
     * Grabs a value from the POST array for the specified field so you can
     * re-populate an input field or textarea
     *
     * @param	string	$field		Field name
     * @param	string	$default	Default value
     * @param	bool	$html_escape	Whether to escape HTML special characters or not
     * @return	string
     */
    function set_value(string $field, string $default = '', bool $html_escape = true): string
    {
        $value = $_POST[$field] ?? $default;
        
        return ($html_escape) ? html_escape($value) : $value;
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('set_select'))
{
    /**
     * Set Select
     *
     * Let's you set the selected value of a <select> menu via data in the POST array.
     * If Form Validation is active it retrieves the info from the validation class
     *
     * @param	string
     * @param	string
     * @param	bool
     * @return	string
     */
    function set_select(string $field, string $value = '', bool $default = false): string
    {
        if (($input = $_POST[$field]) === NULL)
        {
            return ($default === TRUE) ? ' selected="selected"' : '';
        }
        
        if (($input = $_POST[$field]) !== NULL)
        {
            if ( ! is_array($input))
            {
                return ' selected="selected"';
            }
            
            if (is_array($input))
            {
                $value = (string) $value;
                
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
        }
        
        return ($input === $value) ? ' selected="selected"' : '';
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('set_checkbox'))
{
    /**
     * Set Checkbox
     *
     * Let's you set the selected value of a checkbox via the value in the POST array.
     * If Form Validation is active it retrieves the info from the validation class
     *
     * @param	string
     * @param	string
     * @param	bool
     * @return	string
     */
    function set_checkbox(string $field, string $value = '', bool $default = false): string
    {
        // Form inputs are always strings ...
        $value = (string) $value;
        
        $input = $_POST[$field];
        
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
        if ($_POST)
        {
            return ($input === $value) ? ' checked="checked"' : '';
        }
        
        return ($default === TRUE) ? ' checked="checked"' : '';
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('set_radio'))
{
    /**
     * Set Radio
     *
     * Let's you set the selected value of a radio field via info in the POST array.
     * If Form Validation is active it retrieves the info from the validation class
     *
     * @param	string	$field
     * @param	string	$value
     * @param	bool	$default
     * @return	string
     */
    function set_radio(string $field, string $value = '', bool $default = FALSE): string
    {
        // Form inputs are always strings ...
        $value = (string) $value;
        
        $input = $_POST[$field];
        
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
        if ($_POST)
        {
            return ($input === $value) ? ' checked="checked"' : '';
        }
        
        return ($default === TRUE) ? ' checked="checked"' : '';
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('_parse_form_attributes'))
{
    /**
     * Parse the form attributes
     *
     * Helper function used by some of the form helpers
     *
     * @param	array	$attributes	List of attributes
     * @param	array	$default	Default values
     * @return	string
     */
    function _parse_form_attributes($attributes, $default): string
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
            if (count($attributes) > 0)
            {
                $default = array_merge($default, $attributes);
            }
        }
        
        $att = '';
        
        foreach ($default as $key => $val)
        {
            if ($key === 'value')
            {
                $val = htmlspecialchars($val);
            }
            elseif ($key === 'name' && ! strlen($default['name']))
            {
                continue;
            }
            $att .= $key.'="'.$val.'" ';
        }
        
        return $att;
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('_attributes_to_string'))
{
    /**
     * Attributes To String
     *
     * Helper function used by some of the form helpers
     *
     * @param	mixed
     * @return	string
     */
    function _attributes_to_string($attributes): string
    {
        if (empty($attributes))
        {
            return '';
        }
        
        if (is_object($attributes))
        {
            $attributes = (array) $attributes;
        }
        
        if (is_array($attributes))
        {
            $atts = '';
            foreach ($attributes as $key => $val)
            {
                $atts .= ' '.$key.'="'.$val.'"';
            }
            return $atts;
        }
        
        if (is_string($attributes))
        {
            return ' '.$attributes;
        }
        
        return FALSE;
    }
}
