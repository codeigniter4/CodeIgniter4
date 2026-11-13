# Form Helper

The Form Helper file contains functions that assist in working with forms.

## Configuration

Since v4.3.0, void HTML elements (e.g. `<input>`) in `form_helper` functions have been changed to be HTML5-compatible by default. If you need to be compatible with XHTML, you must set the `$html5` property in **app/Config/DocTypes.php** to `false`.

## Loading this Helper

This helper is loaded using the following code:

```php
<?php

helper('form');
```

## Escaping Field Values

You may need to use HTML and characters such as quotes within your form elements. In order to do that safely, you'll need to use the [common function](../general/common_functions.md) `esc()`.

Consider the following example:

```php
<?php

$string = 'Here is a string containing "quoted" text.';

?>

<input type="text" name="myfield" value="<?= $string ?>">
```

Since the above string contains a set of quotes, it will cause the form to break. The `esc()` function converts HTML special characters so that it can be used safely:

```
<input type="text" name="myfield" value="<?= esc($string) ?>">
```

!!! note "Note"
    If you use any of the form helper functions listed on this page, and you pass values as an associative array, the form values will be automatically escaped, so there is no need to call this function. Use it only if you are creating your own form elements, which you would pass as strings.

## Available Functions

The following functions are available:

#### form_open([$action = ''[, $attributes = ''[, $hidden = []]]])

- **Parameters**  
    - **$action** `string` Form action/target URI string
    - **$attributes** `mixed` HTML attributes, as an array or escaped string
    - **$hidden** `array` An array of hidden fields' definitions

- **Returns**: An HTML form opening tag

- **Return type**: `string`

Creates an opening form tag with a site URL **built from your** `Config\App::$baseURL`. It will optionally let you add form attributes and hidden input fields, and will always add the `accept-charset` attribute based on the `$charset` property in your **app/Config/App.php** config file.

The main benefit of using this tag rather than hard coding your own HTML is that it permits your site to be more portable in the event your URLs ever change.

Here's a simple example:

```php
<?php

echo form_open('email/send');
```

The above example would create a form that points to your site URL plus the "email/send" URI segments, like this:

```
<form action="http://example.com/index.php/email/send" method="post" accept-charset="utf-8">
```

You can also add `{locale}` like the following:

```php
<?php

echo form_open('{locale}/email/send');
```

The above example would create a form that points to your site URL plus the current request locale with "email/send" URI segments, like this:

```
<form action="http://example.com/index.php/en/email/send" method="post" accept-charset="utf-8">
```

**Adding Attributes**

Attributes can be added by passing an associative array to the second parameter, like this:

```php
<?php

$attributes = ['class' => 'email', 'id' => 'myform'];
echo form_open('email/send', $attributes);
```

Alternatively, you can specify the second parameter as a string:

```php
<?php

echo form_open('email/send', 'class="email" id="myform"');
```

The above examples would create a form similar to this:

```
<form action="http://example.com/index.php/email/send" class="email" id="myform" method="post" accept-charset="utf-8">
```

If the [CSRF](../libraries/security.md#cross-site-request-forgery-csrf) filter is turned on, `form_open()` will generate a CSRF field at the beginning of the form. You can specify the ID of this field by passing **csrf_id** as an element of the `$attributes` array:

```php
<?php

echo form_open('/u/sign-up', ['csrf_id' => 'my-id']);
```

This will return:

```
<form action="http://example.com/index.php/u/sign-up" method="post" accept-charset="utf-8">
<input type="hidden" id="my-id" name="csrf_test_name" value="964ede6e0ae8a680f7b8eab69136717d">
```

!!! note "Note"
    To use auto-generation of the CSRF field, you need to turn on the [CSRF filter](../libraries/security.md#enable-csrf-protection) in the **app/Config/Filters.php** file. In most cases, the form page is requested using the GET method. Normally, CSRF protection is required for POST/PUT/DELETE/PATCH requests, but even for GET requests, CSRF filters must be enabled for pages that display forms.

    If you enable the CSRF filter with [$globals](../incoming/filters.md#globals), it will be active for all request types. But if you enable the CSRF filter with `public array $methods = ['POST' => ['csrf']];`, the hidden CSRF field will not be added in GET requests.

**Adding Hidden Input Fields**

Hidden fields can be added by passing an associative array to the third parameter, like this:

```php
<?php

$hidden = ['username' => 'Joe', 'member_id' => '234'];
echo form_open('email/send', '', $hidden);
```

You can skip the second parameter by passing any false value to it.

The above example would create a form similar to this:

```
<form action="http://example.com/index.php/email/send" method="post" accept-charset="utf-8">
    <input type="hidden" name="username" value="Joe">
    <input type="hidden" name="member_id" value="234">
```

#### form_open_multipart([$action = ''[, $attributes = ''[, $hidden = []]]])

- **Parameters**  
    - **$action** `string` Form action/target URI string
    - **$attributes** `mixed` HTML attributes, as an array or escaped string
    - **$hidden** `array` An array of hidden fields' definitions

- **Returns**: An HTML multipart form opening tag

- **Return type**: `string`

This function is identical to `form_open()` above, except that it adds a *multipart* attribute, which is necessary if you would like to use the form to upload files.

#### form_hidden($name[, $value = ''])

- **Parameters**  
    - **$name** `string` Field name
    - **$value** `string` Field value

- **Returns**: An HTML hidden input element

- **Return type**: `string`

Lets you generate hidden input fields. You can either submit a name/value string to create one field:

```php
<?php

form_hidden('username', 'johndoe');
// Would produce: <input type="hidden" name="username" value="johndoe">
```

... or you can submit an associative array to create multiple fields:

```php
<?php

$data = [
    'name'  => 'John Doe',
    'email' => 'john@example.com',
    'url'   => 'http://example.com',
];

echo form_hidden($data);
/*
 * Would produce:
 * <input type="hidden" name="name" value="John Doe">
 * <input type="hidden" name="email" value="john@example.com">
 * <input type="hidden" name="url" value="http://example.com">
 */
```

You can also pass an associative array to the value field:

```php
<?php

$data = [
    'name'  => 'John Doe',
    'email' => 'john@example.com',
    'url'   => 'http://example.com',
];

echo form_hidden('my_array', $data);
/*
 * Would produce:
 * <input type="hidden" name="my_array[name]" value="John Doe">
 * <input type="hidden" name="my_array[email]" value="john@example.com">
 * <input type="hidden" name="my_array[url]" value="http://example.com">
 */
```

If you want to create hidden input fields with extra attributes:

```php
<?php

$data = [
    'type'  => 'hidden',
    'name'  => 'email',
    'id'    => 'hiddenemail',
    'value' => 'john@example.com',
    'class' => 'hiddenemail',
];

echo form_input($data);
/*
 * Would produce:
 * <input type="hidden" name="email" value="john@example.com" id="hiddenemail" class="hiddenemail">
 */
```

#### form_input([$data = ''[, $value = ''[, $extra = ''[, $type = 'text']]]])

- **Parameters**  
    - **$data** `array` Field attributes data
    - **$value** `string` Field value
    - **$extra** `mixed` Extra attributes to be added to the tag either as an array or a literal string
    - **$type** `string` The type of input field, e.g., 'text', 'email', 'number', etc.

- **Returns**: An HTML text input element

- **Return type**: `string`

Lets you generate a standard text input field. You can minimally pass the field name and value in the first and second parameters:

```php
<?php

echo form_input('username', 'johndoe');
/*
 * Would produce:
 * <input type="text" name="username" value="johndoe">
 */
```

Or you can pass an associative array containing any data you wish your form to contain:

```php
<?php

$data = [
    'name'      => 'username',
    'id'        => 'username',
    'value'     => 'johndoe',
    'maxlength' => '100',
    'size'      => '50',
    'style'     => 'width:50%',
];
echo form_input($data);
/*
 * Would produce:
 * <input type="text" name="username" value="johndoe" id="username" maxlength="100" size="50" style="width:50%">
 */
```

If you want boolean attributes, pass the boolean value (`true`/`false`). In this case, the boolean value does not matter:

```php
<?php

$data = [
    'name'     => 'username',
    'id'       => 'username',
    'value'    => '',
    'required' => true,
];
echo form_input($data);
/*
 * Would produce:
 * <input type="text" name="username" value="" id="username" required>
 */
```

If you would like your form to contain some additional data, like JavaScript, you can pass it as a string in the third parameter:

```php
<?php

$js = 'onClick="some_function ()"';
echo form_input('username', 'johndoe', $js);
/*
 * Would produce:
 * <input type="text" name="username" value="johndoe" onClick="some_function ()">
 */
```

Or you can pass it as an array:

```php
<?php

$js = ['onClick' => 'some_function ();'];
echo form_input('username', 'johndoe', $js);
/*
 * Would produce:
 * <input type="text" name="username" value="johndoe" onClick="some_function ();">
 */
```

To support the expanded range of HTML5 input fields, you can pass an input type in as the fourth parameter:

```php
<?php

echo form_input('email', 'joe@example.com', ['placeholder' => 'Email Address...'], 'email');
/*
 * Would produce:
 * <input type="email" name="email" value="joe@example.com" placeholder="Email Address...">
 */
```

#### form_password([$data = ''[, $value = ''[, $extra = '']]])

- **Parameters**  
    - **$data** `array` Field attributes data
    - **$value** `string` Field value
    - **$extra** `mixed` Extra attributes to be added to the tag either as an array or a literal string

- **Returns**: An HTML password input element

- **Return type**: `string`

This function is identical in all respects to the `form_input()` function above except that it uses the "password" input type.

#### form_upload([$data = ''[, $value = ''[, $extra = '']]])

- **Parameters**  
    - **$data** `array` Field attributes data
    - **$value** `string` Field value
    - **$extra** `mixed` Extra attributes to be added to the tag either as an array or a literal string

- **Returns**: An HTML file upload input element

- **Return type**: `string`

This function is identical in all respects to the `form_input()` function above except that it uses the "file" input type, allowing it to be used to upload files.

#### form_textarea([$data = ''[, $value = ''[, $extra = '']]])

- **Parameters**  
    - **$data** `array` Field attributes data
    - **$value** `string` Field value
    - **$extra** `mixed` Extra attributes to be added to the tag either as an array or a literal string

- **Returns**: An HTML textarea element

- **Return type**: `string`

This function is identical in all respects to the `form_input()` function above except that it generates a "textarea" type.

!!! note "Note"
    Use the *rows* and *cols* attributes to set the size of the textarea.

#### form_dropdown([$name = ''[, $options = [][, $selected = [][, $extra = '']]]])

- **Parameters**  
    - **$name** `string` Field name
    - **$options** `array` An associative array of options to be listed
    - **$selected** `array` List of fields to mark with the *selected* attribute
    - **$extra** `mixed` Extra attributes to be added to the tag either as an array or a literal string

- **Returns**: An HTML select (dropdown) element

- **Return type**: `string`

Lets you create a standard drop-down field. The first parameter will contain the name of the field, the second parameter will contain an associative array of options, and the third parameter will contain the value you wish to be selected. You can also pass an array of multiple items through the third parameter, and the helper will create a multiple select for you.

Example:

```php
<?php

$options = [
    'small'  => 'Small Shirt',
    'med'    => 'Medium Shirt',
    'large'  => 'Large Shirt',
    'xlarge' => 'Extra Large Shirt',
];

$shirts_on_sale = ['small', 'large'];
echo form_dropdown('shirts', $options, 'large');
/*
 * Would produce:
 * <select name="shirts">
 *     <option value="small">Small Shirt</option>
 *     <option value="med">Medium Shirt</option>
 *     <option value="large" selected="selected">Large Shirt</option>
 *     <option value="xlarge">Extra Large Shirt</option>
 * </select>
 */

echo form_dropdown('shirts', $options, $shirts_on_sale);
/*
 * Would produce:
 * <select name="shirts" multiple="multiple">
 *     <option value="small" selected="selected">Small Shirt</option>
 *     <option value="med">Medium Shirt</option>
 *     <option value="large" selected="selected">Large Shirt</option>
 *     <option value="xlarge">Extra Large Shirt</option>
 * </select>
 */
```

If you would like the opening &lt;select> to contain additional data, like an id attribute or JavaScript, you can pass it as a string in the fourth parameter:

```php
<?php

$js = 'id="shirts" onChange="some_function();"';
echo form_dropdown('shirts', $options, 'large', $js);
```

Or you can pass it as an array:

```php
<?php

$js = [
    'id'       => 'shirts',
    'onChange' => 'some_function();',
];
echo form_dropdown('shirts', $options, 'large', $js);
```

If the array passed as `$options` is a multidimensional array, then `form_dropdown()` will produce an &lt;optgroup> with the array key as the label.

##### Automatic Selection from POST

If no value is passed to `$selected` (or if it is an empty array), `form_dropdown()` and `form_multiselect()` will attempt to infer the selected option(s) directly from the current request's `$_POST` data using the field name (trailing `[]` brackets are automatically stripped).

!!! note "Note"
    This fallback only inspects `$_POST` from the current request. It does not restore old input after a redirect with `redirect()->withInput()`. To restore submitted values after a redirect, pass `set_value()` or `old()` to `$selected` explicitly.

#### form_multiselect([$name = ''[, $options = [][, $selected = [][, $extra = '']]]])

- **Parameters**  
    - **$name** `string` Field name
    - **$options** `array` An associative array of options to be listed
    - **$selected** `array` List of fields to mark with the *selected* attribute
    - **$extra** `mixed` Extra attributes to be added to the tag either as an array or a literal string

- **Returns**: An HTML select element with the multiple attribute

- **Return type**: `string`

Lets you create a standard multiselect field. The first parameter will contain the name of the field, the second parameter will contain an associative array of options, and the third parameter will contain the value or values you wish to be selected.

The parameter usage is identical to using `form_dropdown()` above, except, of course, that the name of the field will need to use POST array syntax, e.g., `shirts[]`.

If `$selected` is omitted, selections are automatically inferred from `$_POST['shirts']`:

```php
<?php

// If $_POST['shirts'] contains ['small', 'large']:
echo form_multiselect('shirts[]', $options);
/*
 * Would produce:
 * <select name="shirts[]" multiple="multiple">
 *     <option value="small" selected="selected">Small Shirt</option>
 *     <option value="med">Medium Shirt</option>
 *     <option value="large" selected="selected">Large Shirt</option>
 *     <option value="xlarge">Extra Large Shirt</option>
 * </select>
 */
```

#### form_fieldset([$legend_text = ''[, $attributes = []]])

- **Parameters**  
    - **$legend_text** `string` Text to put in the &lt;legend> tag
    - **$attributes** `array` Attributes to be set on the &lt;fieldset> tag

- **Returns**: An HTML fieldset opening tag

- **Return type**: `string`

Lets you generate fieldset/legend fields.

Example:

```php
<?php

echo form_fieldset('Address Information');
echo "<p>fieldset content here</p>\n";
echo form_fieldset_close();

?>

<!-- Produces: -->
<fieldset>
    <legend>Address Information</legend>
    <p>form content here</p>
</fieldset>
```

Similar to other functions, you can submit an associative array in the second parameter if you prefer to set additional attributes:

```php
<?php

$attributes = [
    'id'    => 'address_info',
    'class' => 'address_info',
];

echo form_fieldset('Address Information', $attributes);
echo "<p>fieldset content here</p>\n";
echo form_fieldset_close();

?>

<!-- Produces: -->
<fieldset id="address_info" class="address_info">
    <legend>Address Information</legend>
    <p>form content here</p>
</fieldset>
```

#### form_fieldset_close([$extra = ''])

- **Parameters**  
    - **$extra** `string` Anything to append after the closing tag, *as is*

- **Returns**: An HTML fieldset closing tag

- **Return type**: `string`

Produces a closing `</fieldset>` tag. The only advantage to using this function is that it permits you to pass data to it which will be added below the tag. For example:

```php
<?php

$string = '</div></div>';
echo form_fieldset_close($string);
// Would produce: </fieldset></div></div>
```

#### form_checkbox([$data = ''[, $value = ''[, $checked = false[, $extra = '']]]])

- **Parameters**  
    - **$data** `array` Field attributes data
    - **$value** `string` Field value
    - **$checked** `bool` Whether to mark the checkbox as being *checked*
    - **$extra** `mixed` Extra attributes to be added to the tag either as an array or a literal string

- **Returns**: An HTML checkbox input element

- **Return type**: `string`

Lets you generate a checkbox field. Simple example:

```php
<?php

echo form_checkbox('newsletter', 'accept', true);
// Would produce:  <input type="checkbox" name="newsletter" value="accept" checked="checked">
```

The third parameter contains a boolean true/false to determine whether the box should be checked or not.

Similar to the other form functions in this helper, you can also pass an array of attributes to the function:

```php
<?php

$data = [
    'name'    => 'newsletter',
    'id'      => 'newsletter',
    'value'   => 'accept',
    'checked' => true,
    'style'   => 'margin:10px',
];

echo form_checkbox($data);
// Would produce: <input type="checkbox" name="newsletter" id="newsletter" value="accept" checked="checked" style="margin:10px">
```

Also as with other functions, if you would like the tag to contain additional data like JavaScript, you can pass it as a string in the fourth parameter:

```php
<?php

$js = 'onClick="some_function()"';
echo form_checkbox('newsletter', 'accept', true, $js);
```

Or you can pass it as an array:

```php
<?php

$js = ['onClick' => 'some_function();'];
echo form_checkbox('newsletter', 'accept', true, $js);
```

#### form_radio([$data = ''[, $value = ''[, $checked = false[, $extra = '']]]])

- **Parameters**  
    - **$data** `array` Field attributes data
    - **$value** `string` Field value
    - **$checked** `bool` Whether to mark the radio button as being *checked*
    - **$extra** `mixed` Extra attributes to be added to the tag either as an array or a literal string

- **Returns**: An HTML radio input element

- **Return type**: `string`

This function is identical in all respects to the `form_checkbox()` function above except that it uses the "radio" input type.

#### form_label([$label_text = ''[, $id = ''[, $attributes = []]]])

- **Parameters**  
    - **$label_text** `string` Text to put in the &lt;label> tag
    - **$id** `string` ID of the form element that we're making a label for
    - **$attributes** `string` HTML attributes

- **Returns**: An HTML label element

- **Return type**: `string`

Lets you generate a &lt;label>. Simple example:

```php
<?php

echo form_label('What is your Name', 'username');
// Would produce:  <label for="username">What is your Name</label>
```

Similar to other functions, you can submit an associative array in the third parameter if you prefer to set additional attributes.

Example:

```php
<?php

$attributes = [
    'class' => 'mycustomclass',
    'style' => 'color: #000;',
];

echo form_label('What is your Name', 'username', $attributes);
// Would produce:  <label for="username" class="mycustomclass" style="color: #000;">What is your Name</label>
```

#### form_submit([$data = ''[, $value = ''[, $extra = '']]])

- **Parameters**  
    - **$data** `string` Button name
    - **$value** `string` Button value
    - **$extra** `mixed` Extra attributes to be added to the tag either as an array or a literal string

- **Returns**: An HTML input submit element

- **Return type**: `string`

Lets you generate a standard submit button. Simple example:

```php
<?php

echo form_submit('mysubmit', 'Submit Post!');
// Would produce:  <input type="submit" name="mysubmit" value="Submit Post!">
```

Similar to other functions, you can submit an associative array in the first parameter if you prefer to set your own attributes. The third parameter lets you add extra data to your form, like JavaScript.

#### form_reset([$data = ''[, $value = ''[, $extra = '']]])

- **Parameters**  
    - **$data** `string` Button name
    - **$value** `string` Button value
    - **$extra** `mixed` Extra attributes to be added to the tag either as an array or a literal string

- **Returns**: An HTML input reset element

- **Return type**: `string`

Lets you generate a standard reset button. Use is identical to `form_submit()`.

#### form_button([$data = ''[, $content = ''[, $extra = '']]])

- **Parameters**  
    - **$data** `string` Button name
    - **$content** `string` Button label
    - **$extra** `mixed` Extra attributes to be added to the tag either as an array or a literal string

- **Returns**: An HTML button element

- **Return type**: `string`

Lets you generate a standard button element. You can minimally pass the button name and content in the first and second parameters:

```php
<?php

echo form_button('name', 'content');
// Would produce: <button name="name" type="button">Content</button>
```

Or you can pass an associative array containing any data you wish your form to contain:

```php
<?php

$data = [
    'name'    => 'button',
    'id'      => 'button',
    'value'   => 'true',
    'type'    => 'reset',
    'content' => 'Reset',
];

echo form_button($data);
// Would produce: <button name="button" id="button" value="true" type="reset">Reset</button>
```

If you would like your form to contain some additional data, like JavaScript, you can pass it as a string in the third parameter:

```php
<?php

$js = 'onClick="some_function()"';
echo form_button('mybutton', 'Click Me', $js);
```

#### form_close([$extra = ''])

- **Parameters**  
    - **$extra** `string` Anything to append after the closing tag, *as is*

- **Returns**: An HTML form closing tag

- **Return type**: `string`

Produces a closing `</form>` tag. The only advantage to using this function is that it permits you to pass data to it which will be added below the tag. For example:

```php
<?php

$string = '</div></div>';
echo form_close($string);
// Would produce:  </form> </div></div>
```

#### set_value($field[, $default = ''[, $html_escape = true]])

- **Parameters**  
    - **$field** `string` Field name
    - **$default** `mixed` Default value. An array is returned as is, any other value is converted to a string.
    - **$html_escape** `bool` Whether to turn off HTML escaping of the value

- **Returns**: Field value

- **Return type**: `array|string`

Permits you to set the value of an input or textarea element. You must supply the field name via the first parameter of the function. The second (optional) parameter allows you to set a default value for the field value. The third (optional) parameter allows you to turn off HTML escaping of the value, in case you need to use this function in combination with e.g., `form_input()` and avoid double-escaping.

Example:

```
<input type="text" name="quantity" value="<?= set_value('quantity', '0') ?>" size="50">
```

The above form will show "0" when loaded for the first time.

#### set_select($field[, $value = ''[, $default = false]])

- **Parameters**  
    - **$field** `string` Field name
    - **$value** `string` Value to check for
    - **$default** `bool` Whether the value is also a default one

- **Returns**: 'selected' attribute or an empty string

- **Return type**: `string`

If you use a &lt;select> menu, this function permits you to display the menu item that was selected.

The first parameter must contain the name of the select menu, the second parameter must contain the value of each item, and the third (optional) parameter lets you set an item as the default (use boolean true/false).

Example:

```
<select name="myselect">
    <option value="one" <?= set_select('myselect', 'one', true) ?>>One</option>
    <option value="two" <?= set_select('myselect', 'two') ?>>Two</option>
    <option value="three" <?= set_select('myselect', 'three') ?>>Three</option>
</select>
```

#### set_checkbox($field[, $value = ''[, $default = false]])

- **Parameters**  
    - **$field** `string` Field name
    - **$value** `string` Value to check for
    - **$default** `bool` Whether the value is also a default one

- **Returns**: 'checked' attribute or an empty string

- **Return type**: `string`

Permits you to display a checkbox in the state in which it was submitted.

The first parameter must contain the name of the checkbox, the second parameter must contain its value, and the third (optional) parameter lets you set an item as the default (use boolean true/false).

Example:

```
<input type="checkbox" name="mycheck[]" value="1" <?= set_checkbox('mycheck', '1') ?>>
<input type="checkbox" name="mycheck[]" value="2" <?= set_checkbox('mycheck', '2') ?>>
```

#### set_radio($field[, $value = ''[, $default = false]])

- **Parameters**  
    - **$field** `string` Field name
    - **$value** `string` Value to check for
    - **$default** `bool` Whether the value is also a default one

- **Returns**: 'checked' attribute or an empty string

- **Return type**: `string`

Permits you to display radio buttons in the state in which they were submitted. This function is identical to the `set_checkbox()` function above.

Example:

```
<input type="radio" name="myradio" value="1" <?= set_radio('myradio', '1', true) ?>>
<input type="radio" name="myradio" value="2" <?= set_radio('myradio', '2') ?>>
```

#### validation_errors()

!!! success "Available from version 4.3.0"

- **Returns**: The validation errors
- **Return type**: `array`

Returns the validation errors. First, this function checks the validation errors that are stored in the session. To store the errors in the session, you need to use `withInput()` with `redirect()`.

The returned array is the same as `Validation::getErrors()`. See [Validation](../libraries/validation.md#redirect-and-validation-errors) for details.

!!! note "Note"
    This function does not work with [In-Model Validation](../models/model.md#in-model-validation). If you want to get the validation errors in model validation, see [Getting Validation Errors](../models/model.md#getting-validation-errors).

Example:

```
<?php $errors = validation_errors(); ?>
```

#### validation_list_errors($template = 'list')

!!! success "Available from version 4.3.0"

- **Parameters**  
    - **$template** `string` Validation template name

- **Returns**: Rendered HTML of the validation errors

- **Return type**: `string`

Returns the rendered HTML of the validation errors.

The parameter `$template` is a Validation template name. See [Customizing Error Display](../libraries/validation.md#customizing-error-display) for details.

This function uses `validation_errors()` internally.

!!! note "Note"
    This function does not work with [In-Model Validation](../models/model.md#in-model-validation). If you want to get the validation errors in model validation, see [Getting Validation Errors](../models/model.md#getting-validation-errors).

Example:

```
<?= validation_list_errors() ?>
```

#### validation_show_error($field, $template = 'single')

!!! success "Available from version 4.3.0"

- **Parameters**  
    - **$field** `string` Field name
    - **$template** `string` Validation template name

- **Returns**: Rendered HTML of the validation error

- **Return type**: `string`

Returns a single error for the specified field in formatted HTML.

The parameter `$template` is a Validation template name. See [Customizing Error Display](../libraries/validation.md#customizing-error-display) for details.

This function uses `validation_errors()` internally.

!!! note "Note"
    This function does not work with [In-Model Validation](../models/model.md#in-model-validation). If you want to get the validation errors in model validation, see [Getting Validation Errors](../models/model.md#getting-validation-errors).

Example:

```
<?= validation_show_error('username') ?>
```
