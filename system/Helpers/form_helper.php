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

use CodeIgniter\Validation\Exceptions\ValidationException;
use Config\App;
use Config\Validation;

// CodeIgniter Form Helpers

if (! function_exists('form_open')) {
    /**
     * Form Declaration
     *
     * Creates the opening portion of the form.
     *
     * @param string       $action     the URI segments of the form destination
     * @param array|string $attributes a key/value pair of attributes, or string representation
     * @param array        $hidden     a key/value pair hidden data
     */
    function form_open(string $action = '', $attributes = [], array $hidden = []): string
    {
        // If no action is provided then set to the current url
        if ($action === '') {
            $action = (string) current_url(true);
        } // If an action is not a full URL then turn it into one
        elseif (! str_contains($action, '://')) {
            // If an action has {locale}
            if (str_contains($action, '{locale}')) {
                $action = str_replace('{locale}', service('request')->getLocale(), $action);
            }

            $action = site_url($action);
        }

        if (is_array($attributes) && array_key_exists('csrf_id', $attributes)) {
            $csrfId = $attributes['csrf_id'];
            unset($attributes['csrf_id']);
        }

        $attributes = stringify_attributes($attributes);

        if (! str_contains(strtolower($attributes), 'method=')) {
            $attributes .= ' method="post"';
        }
        if (! str_contains(strtolower($attributes), 'accept-charset=')) {
            $config = config(App::class);
            $attributes .= ' accept-charset="' . strtolower($config->charset) . '"';
        }

        $form = '<form action="' . $action . '"' . $attributes . ">\n";

        // Add CSRF field if enabled, but leave it out for GET requests and requests to external websites
        $before = service('filters')->getFilters()['before'];

        if ((in_array('csrf', $before, true) || array_key_exists('csrf', $before)) && str_contains($action, base_url()) && ! str_contains(strtolower($form), strtolower('method="get"'))) {
            $form .= csrf_field($csrfId ?? null);
        }

        foreach ($hidden as $name => $value) {
            $form .= form_hidden($name, $value);
        }

        return $form;
    }
}

if (! function_exists('form_open_multipart')) {
    /**
     * Form Declaration - Multipart type
     *
     * Creates the opening portion of the form, but with "multipart/form-data".
     *
     * @param string       $action     The URI segments of the form destination
     * @param array|string $attributes A key/value pair of attributes, or the same as a string
     * @param array        $hidden     A key/value pair hidden data
     */
    function form_open_multipart(string $action = '', $attributes = [], array $hidden = []): string
    {
        if (is_string($attributes)) {
            $attributes .= ' enctype="' . esc('multipart/form-data') . '"';
        } else {
            $attributes['enctype'] = 'multipart/form-data';
        }

        return form_open($action, $attributes, $hidden);
    }
}

if (! function_exists('form_hidden')) {
    /**
     * Hidden Input Field
     *
     * Generates hidden fields. You can pass a simple key/value string or
     * an associative array with multiple values.
     *
     * @param array|string $name  Field name or associative array to create multiple fields
     * @param array|string $value Field value
     */
    function form_hidden($name, $value = '', bool $recursing = false): string
    {
        static $form;

        if ($recursing === false) {
            $form = "\n";
        }

        if (is_array($name)) {
            foreach ($name as $key => $val) {
                form_hidden($key, $val, true);
            }

            return $form;
        }

        if (! is_array($value)) {
            $form .= form_input($name, $value, '', 'hidden');
        } else {
            foreach ($value as $k => $v) {
                $k = is_int($k) ? '' : $k;
                form_hidden($name . '[' . $k . ']', $v, true);
            }
        }

        return $form;
    }
}

if (! function_exists('form_input')) {
    /**
     * Text Input Field. If 'type' is passed in the $type field, it will be
     * used as the input type, for making 'email', 'phone', etc input fields.
     *
     * @param array|string        $data
     * @param array|object|string $extra string, array, object that can be cast to array
     */
    function form_input($data = '', string $value = '', $extra = '', string $type = 'text'): string
    {
        $defaults = [
            'type'  => $type,
            'name'  => is_array($data) ? '' : $data,
            'value' => $value,
        ];

        return '<input ' . parse_form_attributes($data, $defaults) . stringify_attributes($extra) . _solidus() . ">\n";
    }
}

if (! function_exists('form_password')) {
    /**
     * Password Field
     *
     * Identical to the input function but adds the "password" type
     *
     * @param array|string        $data
     * @param array|object|string $extra string, array, object that can be cast to array
     */
    function form_password($data = '', string $value = '', $extra = ''): string
    {
        if (! is_array($data)) {
            $data = ['name' => $data];
        }
        $data['type'] = 'password';

        return form_input($data, $value, $extra);
    }
}

if (! function_exists('form_upload')) {
    /**
     * Upload Field
     *
     * Identical to the input function but adds the "file" type
     *
     * @param array|string        $data
     * @param array|object|string $extra string, array, object that can be cast to array
     */
    function form_upload($data = '', string $value = '', $extra = ''): string
    {
        $defaults = [
            'type' => 'file',
            'name' => '',
        ];

        if (! is_array($data)) {
            $data = ['name' => $data];
        }

        $data['type'] = 'file';

        return '<input ' . parse_form_attributes($data, $defaults) . stringify_attributes($extra) . _solidus() . ">\n";
    }
}

if (! function_exists('form_textarea')) {
    /**
     * Textarea field
     *
     * @param array|string        $data
     * @param array|object|string $extra string, array, object that can be cast to array
     */
    function form_textarea($data = '', string $value = '', $extra = ''): string
    {
        $defaults = [
            'name' => is_array($data) ? '' : $data,
            'cols' => '40',
            'rows' => '10',
        ];
        if (! is_array($data) || ! isset($data['value'])) {
            $val = $value;
        } else {
            $val = $data['value'];
            unset($data['value']); // textareas don't use the value attribute
        }

        // Unsets default rows and cols if defined in extra field as array or string.
        if ((is_array($extra) && array_key_exists('rows', $extra)) || (is_string($extra) && str_contains(strtolower(preg_replace('/\s+/', '', $extra)), 'rows='))) {
            unset($defaults['rows']);
        }

        if ((is_array($extra) && array_key_exists('cols', $extra)) || (is_string($extra) && str_contains(strtolower(preg_replace('/\s+/', '', $extra)), 'cols='))) {
            unset($defaults['cols']);
        }

        return '<textarea ' . rtrim(parse_form_attributes($data, $defaults)) . stringify_attributes($extra) . '>'
                . htmlspecialchars($val)
                . "</textarea>\n";
    }
}

if (! function_exists('form_multiselect')) {
    /**
     * Multi-select menu
     *
     * @param array|string        $name
     * @param array|object|string $extra string, array, object that can be cast to array
     */
    function form_multiselect($name = '', array $options = [], array $selected = [], $extra = ''): string
    {
        $extra = stringify_attributes($extra);

        if (! str_contains(strtolower($extra), strtolower('multiple'))) {
            $extra .= ' multiple="multiple"';
        }

        return form_dropdown($name, $options, $selected, $extra);
    }
}

if (! function_exists('form_dropdown')) {
    /**
     * Drop-down Menu
     *
     * @param array|string        $data
     * @param array|string        $options
     * @param array|string        $selected
     * @param array|object|string $extra    string, array, object that can be cast to array
     */
    function form_dropdown($data = '', $options = [], $selected = [], $extra = ''): string
    {
        $defaults = [];
        if (is_array($data)) {
            if (isset($data['selected'])) {
                $selected = $data['selected'];
                unset($data['selected']); // select tags don't have a selected attribute
            }
            if (isset($data['options'])) {
                $options = $data['options'];
                unset($data['options']); // select tags don't use an options attribute
            }
        } else {
            $defaults = ['name' => $data];
        }

        if (! is_array($selected)) {
            $selected = [$selected];
        }
        if (! is_array($options)) {
            $options = [$options];
        }

        // If no selected state was submitted we will attempt to set it automatically
        if ($selected === []) {
            $superglobals = service('superglobals');
            if (is_array($data)) {
                $postValue = $superglobals->post($data['name'] ?? '');
                if (isset($data['name']) && $postValue !== null) {
                    $selected = [$postValue];
                }
            } else {
                $postValue = $superglobals->post($data);
                if ($postValue !== null) {
                    $selected = [$postValue];
                }
            }
        }

        // Standardize selected as strings, like the option keys will be
        foreach ($selected as $key => $item) {
            $selected[$key] = (string) $item;
        }

        $extra    = stringify_attributes($extra);
        $multiple = (count($selected) > 1 && ! str_contains(strtolower($extra), 'multiple')) ? ' multiple="multiple"' : '';
        $form     = '<select ' . rtrim(parse_form_attributes($data, $defaults)) . $extra . $multiple . ">\n";

        foreach ($options as $key => $val) {
            // Keys should always be strings for strict comparison
            $key = (string) $key;

            if (is_array($val)) {
                if ($val === []) {
                    continue;
                }

                $form .= '<optgroup label="' . $key . "\">\n";

                foreach ($val as $optgroupKey => $optgroupVal) {
                    // Keys should always be strings for strict comparison
                    $optgroupKey = (string) $optgroupKey;

                    $sel = in_array($optgroupKey, $selected, true) ? ' selected="selected"' : '';
                    $form .= '<option value="' . htmlspecialchars($optgroupKey) . '"' . $sel . '>' . $optgroupVal . "</option>\n";
                }

                $form .= "</optgroup>\n";
            } else {
                $form .= '<option value="' . htmlspecialchars($key) . '"'
                    . (in_array($key, $selected, true) ? ' selected="selected"' : '') . '>'
                    . $val . "</option>\n";
            }
        }

        return $form . "</select>\n";
    }
}

if (! function_exists('form_checkbox')) {
    /**
     * Checkbox Field
     *
     * @param array|string        $data
     * @param array|object|string $extra string, array, object that can be cast to array
     */
    function form_checkbox($data = '', string $value = '', bool $checked = false, $extra = ''): string
    {
        $defaults = [
            'type'  => 'checkbox',
            'name'  => is_array($data) ? '' : $data,
            'value' => $value,
        ];

        if (is_array($data) && array_key_exists('checked', $data)) {
            $checked = $data['checked'];

            if ($checked === false) {
                unset($data['checked']);
            } else {
                $data['checked'] = 'checked';
            }
        }

        if ($checked === true) {
            $defaults['checked'] = 'checked';
        }

        return '<input ' . parse_form_attributes($data, $defaults) . stringify_attributes($extra) . _solidus() . ">\n";
    }
}

if (! function_exists('form_radio')) {
    /**
     * Radio Button
     *
     * @param array|string        $data
     * @param array|object|string $extra string, array, object that can be cast to array
     */
    function form_radio($data = '', string $value = '', bool $checked = false, $extra = ''): string
    {
        if (! is_array($data)) {
            $data = ['name' => $data];
        }
        $data['type'] = 'radio';

        return form_checkbox($data, $value, $checked, $extra);
    }
}

if (! function_exists('form_submit')) {
    /**
     * Submit Button
     *
     * @param array|string        $data
     * @param array|object|string $extra string, array, object that can be cast to array
     */
    function form_submit($data = '', string $value = '', $extra = ''): string
    {
        return form_input($data, $value, $extra, 'submit');
    }
}

if (! function_exists('form_reset')) {
    /**
     * Reset Button
     *
     * @param array|string        $data
     * @param array|object|string $extra string, array, object that can be cast to array
     */
    function form_reset($data = '', string $value = '', $extra = ''): string
    {
        return form_input($data, $value, $extra, 'reset');
    }
}

if (! function_exists('form_button')) {
    /**
     * Form Button
     *
     * @param array|string        $data
     * @param array|object|string $extra string, array, object that can be cast to array
     */
    function form_button($data = '', string $content = '', $extra = ''): string
    {
        $defaults = [
            'name' => is_array($data) ? '' : $data,
            'type' => 'button',
        ];

        if (is_array($data) && isset($data['content'])) {
            $content = $data['content'];
            unset($data['content']); // content is not an attribute
        }

        return '<button ' . parse_form_attributes($data, $defaults) . stringify_attributes($extra) . '>'
                . $content
                . "</button>\n";
    }
}

if (! function_exists('form_label')) {
    /**
     * Form Label Tag
     *
     * @param string $labelText  The text to appear onscreen
     * @param string $id         The id the label applies to
     * @param array  $attributes Additional attributes
     */
    function form_label(string $labelText = '', string $id = '', array $attributes = []): string
    {
        $label = '<label';

        if ($id !== '') {
            $label .= ' for="' . $id . '"';
        }

        foreach ($attributes as $key => $val) {
            $label .= ' ' . $key . '="' . $val . '"';
        }

        return $label . '>' . $labelText . '</label>';
    }
}

if (! function_exists('form_datalist')) {
    /**
     * Datalist
     *
     * The <datalist> element specifies a list of pre-defined options for an <input> element.
     * Users will see a drop-down list of pre-defined options as they input data.
     * The list attribute of the <input> element, must refer to the id attribute of the <datalist> element.
     */
    function form_datalist(string $name, string $value, array $options): string
    {
        $data = [
            'type'  => 'text',
            'name'  => $name,
            'list'  => $name . '_list',
            'value' => $value,
        ];

        $out = form_input($data) . "\n";

        $out .= "<datalist id='" . $name . "_list'>";

        foreach ($options as $option) {
            $out .= "<option value='{$option}'>\n";
        }

        return $out . ("</datalist>\n");
    }
}

if (! function_exists('form_fieldset')) {
    /**
     * Fieldset Tag
     *
     * Used to produce <fieldset><legend>text</legend>.  To close fieldset
     * use form_fieldset_close()
     *
     * @param string $legendText The legend text
     * @param array  $attributes Additional attributes
     */
    function form_fieldset(string $legendText = '', array $attributes = []): string
    {
        $fieldset = '<fieldset' . stringify_attributes($attributes) . ">\n";

        if ($legendText !== '') {
            return $fieldset . '<legend>' . $legendText . "</legend>\n";
        }

        return $fieldset;
    }
}

if (! function_exists('form_fieldset_close')) {
    /**
     * Fieldset Close Tag
     */
    function form_fieldset_close(string $extra = ''): string
    {
        return '</fieldset>' . $extra;
    }
}

if (! function_exists('form_close')) {
    /**
     * Form Close Tag
     */
    function form_close(string $extra = ''): string
    {
        return '</form>' . $extra;
    }
}

if (! function_exists('set_value')) {
    /**
     * Form Value
     *
     * Grabs a value from the POST array for the specified field so you can
     * re-populate an input field or textarea
     *
     * @param string              $field      Field name
     * @param list<string>|string $default    Default value
     * @param bool                $htmlEscape Whether to escape HTML special characters or not
     *
     * @return list<string>|string
     */
    function set_value(string $field, $default = '', bool $htmlEscape = true)
    {
        $request = service('request');

        // Try any old input data we may have first
        $value = $request->getOldInput($field);

        if ($value === null) {
            $value = $request->getPost($field) ?? $default;
        }

        return ($htmlEscape) ? esc($value) : $value;
    }
}

if (! function_exists('set_select')) {
    /**
     * Set Select
     *
     * Let's you set the selected value of a <select> menu via data in the POST array.
     */
    function set_select(string $field, string $value = '', bool $default = false): string
    {
        $request = service('request');

        // Try any old input data we may have first
        $input = $request->getOldInput($field);

        if ($input === null) {
            $input = $request->getPost($field);
        }

        if ($input === null) {
            return $default ? ' selected="selected"' : '';
        }

        if (is_array($input)) {
            // Note: in_array('', array(0)) returns TRUE, do not use it
            foreach ($input as &$v) {
                if ($value === $v) {
                    return ' selected="selected"';
                }
            }

            return '';
        }

        return ($input === $value) ? ' selected="selected"' : '';
    }
}

if (! function_exists('set_checkbox')) {
    /**
     * Set Checkbox
     *
     * Let's you set the selected value of a checkbox via the value in the POST array.
     */
    function set_checkbox(string $field, string $value = '', bool $default = false): string
    {
        $request = service('request');

        // Try any old input data we may have first
        $input = $request->getOldInput($field);

        if ($input === null) {
            $input = $request->getPost($field);
        }

        if (is_array($input)) {
            // Note: in_array('', array(0)) returns TRUE, do not use it
            foreach ($input as &$v) {
                if ($value === $v) {
                    return ' checked="checked"';
                }
            }

            return '';
        }

        $session     = service('session');
        $hasOldInput = $session->has('_ci_old_input');

        // Unchecked checkbox and radio inputs are not even submitted by browsers ...
        if ((string) $input === '0' || ! empty($request->getPost()) || $hasOldInput) {
            return ($input === $value) ? ' checked="checked"' : '';
        }

        return $default ? ' checked="checked"' : '';
    }
}

if (! function_exists('set_radio')) {
    /**
     * Set Radio
     *
     * Let's you set the selected value of a radio field via info in the POST array.
     */
    function set_radio(string $field, string $value = '', bool $default = false): string
    {
        $request = service('request');

        // Try any old input data we may have first
        $oldInput = $request->getOldInput($field);

        $postInput = $request->getPost($field);

        $input = $oldInput ?? $postInput ?? $default;

        if (is_array($input)) {
            // Note: in_array('', array(0)) returns TRUE, do not use it
            foreach ($input as $v) {
                if ($value === $v) {
                    return ' checked="checked"';
                }
            }

            return '';
        }

        // Unchecked checkbox and radio inputs are not even submitted by browsers ...
        if ($oldInput !== null || $postInput !== null) {
            return ((string) $input === $value) ? ' checked="checked"' : '';
        }

        return $default ? ' checked="checked"' : '';
    }
}

if (! function_exists('validation_errors')) {
    /**
     * Returns the validation errors.
     *
     * First, checks the validation errors that are stored in the session.
     * To store the errors in the session, you need to use `withInput()` with `redirect()`.
     *
     * The returned array should be in the following format:
     *     [
     *         'field1' => 'error message',
     *         'field2' => 'error message',
     *     ]
     *
     * @return array<string, string>
     */
    function validation_errors()
    {
        $errors = session('_ci_validation_errors');

        // Check the session to see if any were
        // passed along from a redirect withErrors() request.
        if ($errors !== null && (ENVIRONMENT === 'testing' || ! is_cli())) {
            return $errors;
        }

        $validation = service('validation');

        return $validation->getErrors();
    }
}

if (! function_exists('validation_list_errors')) {
    /**
     * Returns the rendered HTML of the validation errors.
     *
     * See Validation::listErrors()
     */
    function validation_list_errors(string $template = 'list'): string
    {
        $config = config(Validation::class);
        $view   = service('renderer');

        if (! array_key_exists($template, $config->templates)) {
            throw ValidationException::forInvalidTemplate($template);
        }

        return $view->setVar('errors', validation_errors())
            ->render($config->templates[$template]);
    }
}

if (! function_exists('validation_show_error')) {
    /**
     * Returns a single error for the specified field in formatted HTML.
     *
     * See Validation::showError()
     */
    function validation_show_error(string $field, string $template = 'single'): string
    {
        $config = config(Validation::class);
        $view   = service('renderer');

        $errors = array_filter(validation_errors(), static fn ($key): bool => preg_match(
            '/^' . str_replace(['\.\*', '\*\.'], ['\..+', '.+\.'], preg_quote($field, '/')) . '$/',
            $key,
        ) === 1, ARRAY_FILTER_USE_KEY);

        if ($errors === []) {
            return '';
        }

        if (! array_key_exists($template, $config->templates)) {
            throw ValidationException::forInvalidTemplate($template);
        }

        return $view->setVar('error', implode("\n", $errors))
            ->render($config->templates[$template]);
    }
}

if (! function_exists('parse_form_attributes')) {
    /**
     * Parse the form attributes
     *
     * Helper function used by some of the form helpers
     *
     * @internal
     *
     * @param array|string $attributes List of attributes
     * @param array        $default    Default values
     */
    function parse_form_attributes($attributes, array $default): string
    {
        if (is_array($attributes)) {
            foreach (array_keys($default) as $key) {
                if (isset($attributes[$key])) {
                    $default[$key] = $attributes[$key];
                    unset($attributes[$key]);
                }
            }
            if ($attributes !== []) {
                $default = array_merge($default, $attributes);
            }
        }

        $att = '';

        foreach ($default as $key => $val) {
            if (! is_bool($val)) {
                if ($key === 'value') {
                    $val = esc($val);
                } elseif ($key === 'name' && $default['name'] === '') {
                    continue;
                }
                $att .= $key . '="' . $val . '"' . ($key === array_key_last($default) ? '' : ' ');
            } else {
                $att .= $key . ' ';
            }
        }

        return $att;
    }
}
