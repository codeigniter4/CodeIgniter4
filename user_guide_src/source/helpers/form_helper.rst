###########
Form Helper
###########

The Form Helper file contains functions that assist in working with
forms.

.. contents::
    :local:
    :depth: 2

*************
Configuration
*************

Since v4.3.0, void HTML elements (e.g. ``<input>``) in ``form_helper`` functions have been changed to be HTML5-compatible by default and if you need to be compatible with XHTML, you must set the ``$html5`` property in **app/Config/DocTypes.php** to ``false``.

*******************
Loading this Helper
*******************

This helper is loaded using the following code:

.. literalinclude:: form_helper/001.php

*********************
Escaping Field Values
*********************

You may need to use HTML and characters such as quotes within your form
elements. In order to do that safely, you'll need to use
:doc:`common function <../general/common_functions>`
:php:func:`esc()`.

Consider the following example:

.. literalinclude:: form_helper/002.php

Since the above string contains a set of quotes, it will cause the form
to break. The :php:func:`esc()` function converts HTML special
characters so that it can be used safely::

    <input type="text" name="myfield" value="<?= esc($string) ?>">

.. note:: If you use any of the form helper functions listed on this page,
    and you pass values as an associative array,
    the form values will be automatically escaped, so there is no need
    to call this function. Use it only if you are creating your own
    form elements, which you would pass as strings.

*******************
Available Functions
*******************

The following functions are available:

.. php:function:: form_open([$action = ''[, $attributes = ''[, $hidden = []]]])

    :param    string    $action: Form action/target URI string
    :param    mixed    $attributes: HTML attributes, as an array or escaped string
    :param    array    $hidden: An array of hidden fields' definitions
    :returns:    An HTML form opening tag
    :rtype:    string

    Creates an opening form tag with a site URL **built from your** ``Config\App::$baseURL``.
    It will optionally let you add form attributes and hidden input fields, and
    will always add the `accept-charset` attribute based on the charset value in your
    config file.

    The main benefit of using this tag rather than hard coding your own HTML is that
    it permits your site to be more portable in the event your URLs ever change.

    Here's a simple example:

    .. literalinclude:: form_helper/003.php

    The above example would create a form that points to your site URL plus the
    "email/send" URI segments, like this::

        <form action="http://example.com/index.php/email/send" method="post" accept-charset="utf-8">

    You can also add ``{locale}`` like the following:

    .. literalinclude:: form_helper/004.php

    The above example would create a form that points to your site URL plus the current request locale with
    "email/send" URI segments, like this::

        <form action="http://example.com/index.php/en/email/send" method="post" accept-charset="utf-8">

    **Adding Attributes**

        Attributes can be added by passing an associative array to the second
        parameter, like this:

        .. literalinclude:: form_helper/005.php

        Alternatively, you can specify the second parameter as a string:

        .. literalinclude:: form_helper/006.php

        The above examples would create a form similar to this::

            <form action="http://example.com/index.php/email/send" class="email" id="myform" method="post" accept-charset="utf-8">

        If :ref:`CSRF <cross-site-request-forgery>` filter is turned on ``form_open()`` will generate CSRF field at the beginning of the form. You can specify ID of this field by passing csrf_id as one of the ``$attribute`` array:

        .. literalinclude:: form_helper/007.php

        will return::

            <form action="http://example.com/index.php/u/sign-up" method="post" accept-charset="utf-8">
            <input type="hidden" id="my-id" name="csrf_field" value="964ede6e0ae8a680f7b8eab69136717d">

        .. note:: To use auto-generation of CSRF field, you need to turn CSRF filter on to the form page. In most cases it is requested using the ``GET`` method.

    **Adding Hidden Input Fields**

        Hidden fields can be added by passing an associative array to the
        third parameter, like this:

        .. literalinclude:: form_helper/008.php

        You can skip the second parameter by passing any false value to it.

        The above example would create a form similar to this::

            <form action="http://example.com/index.php/email/send" method="post" accept-charset="utf-8">
                <input type="hidden" name="username" value="Joe">
                <input type="hidden" name="member_id" value="234">

.. php:function:: form_open_multipart([$action = ''[, $attributes = ''[, $hidden = []]]])

    :param    string    $action: Form action/target URI string
    :param    mixed    $attributes: HTML attributes, as an array or escaped string
    :param    array    $hidden: An array of hidden fields' definitions
    :returns:    An HTML multipart form opening tag
    :rtype:    string

    This function is identical to :php:func:`form_open()` above,
    except that it adds a *multipart* attribute, which is necessary if you
    would like to use the form to upload files with.

.. php:function:: form_hidden($name[, $value = ''])

    :param    string    $name: Field name
    :param    string    $value: Field value
    :returns:    An HTML hidden input field tag
    :rtype:    string

    Lets you generate hidden input fields. You can either submit a
    name/value string to create one field:

    .. literalinclude:: form_helper/009.php

    ... or you can submit an associative array to create multiple fields:

    .. literalinclude:: form_helper/010.php

    You can also pass an associative array to the value field:

    .. literalinclude:: form_helper/011.php

    If you want to create hidden input fields with extra attributes:

    .. literalinclude:: form_helper/012.php

.. php:function:: form_input([$data = ''[, $value = ''[, $extra = ''[, $type = 'text']]]])

    :param    array    $data: Field attributes data
    :param    string    $value: Field value
    :param    mixed    $extra: Extra attributes to be added to the tag either as an array or a literal string
    :param  string  $type: The type of input field. i.e., 'text', 'email', 'number', etc.
    :returns:    An HTML text input field tag
    :rtype:    string

    Lets you generate a standard text input field. You can minimally pass
    the field name and value in the first and second parameter:

    .. literalinclude:: form_helper/013.php

    Or you can pass an associative array containing any data you wish your
    form to contain:

    .. literalinclude:: form_helper/014.php

    If you want boolean attributes, pass the boolean value (``true``/``false``). In this case the boolean value does not matter:

    .. literalinclude:: form_helper/035.php

    If you would like your form to contain some additional data, like
    JavaScript, you can pass it as a string in the third parameter:

    .. literalinclude:: form_helper/015.php

    Or you can pass it as an array:

    .. literalinclude:: form_helper/016.php

    To support the expanded range of HTML5 input fields, you can pass an input type in as the fourth parameter:

    .. literalinclude:: form_helper/017.php

.. php:function:: form_password([$data = ''[, $value = ''[, $extra = '']]])

    :param    array    $data: Field attributes data
    :param    string    $value: Field value
    :param    mixed    $extra: Extra attributes to be added to the tag either as an array or a literal string
    :returns:    An HTML password input field tag
    :rtype:    string

    This function is identical in all respects to the :php:func:`form_input()`
    function above except that it uses the "password" input type.

.. php:function:: form_upload([$data = ''[, $value = ''[, $extra = '']]])

    :param    array    $data: Field attributes data
    :param    string    $value: Field value
    :param    mixed    $extra: Extra attributes to be added to the tag either as an array or a literal string
    :returns:    An HTML file upload input field tag
    :rtype:    string

    This function is identical in all respects to the :php:func:`form_input()`
    function above except that it uses the "file" input type, allowing it to
    be used to upload files.

.. php:function:: form_textarea([$data = ''[, $value = ''[, $extra = '']]])

    :param    array    $data: Field attributes data
    :param    string    $value: Field value
    :param    mixed    $extra: Extra attributes to be added to the tag either as an array or a literal string
    :returns:    An HTML textarea tag
    :rtype:    string

    This function is identical in all respects to the :php:func:`form_input()`
    function above except that it generates a "textarea" type.

    .. note:: Instead of the *maxlength* and *size* attributes in the above example,
        you will instead specify *rows* and *cols*.

.. php:function:: form_dropdown([$name = ''[, $options = [][, $selected = [][, $extra = '']]]])

    :param    string    $name: Field name
    :param    array    $options: An associative array of options to be listed
    :param    array    $selected: List of fields to mark with the *selected* attribute
    :param    mixed    $extra: Extra attributes to be added to the tag either as an array or a literal string
    :returns:    An HTML dropdown select field tag
    :rtype:    string

    Lets you create a standard drop-down field. The first parameter will
    contain the name of the field, the second parameter will contain an
    associative array of options, and the third parameter will contain the
    value you wish to be selected. You can also pass an array of multiple
    items through the third parameter, and the helper will create a
    multiple select for you.

    Example:

    .. literalinclude:: form_helper/018.php

    If you would like the opening <select> to contain additional data, like
    an id attribute or JavaScript, you can pass it as a string in the fourth
    parameter:

    .. literalinclude:: form_helper/019.php

    Or you can pass it as an array:

    .. literalinclude:: form_helper/020.php

    If the array passed as ``$options`` is a multidimensional array, then
    ``form_dropdown()`` will produce an <optgroup> with the array key as the
    label.

.. php:function:: form_multiselect([$name = ''[, $options = [][, $selected = [][, $extra = '']]]])

    :param    string    $name: Field name
    :param    array    $options: An associative array of options to be listed
    :param    array    $selected: List of fields to mark with the *selected* attribute
    :param    mixed    $extra: Extra attributes to be added to the tag either as an array or a literal string
    :returns:    An HTML dropdown multiselect field tag
    :rtype:    string

    Lets you create a standard multiselect field. The first parameter will
    contain the name of the field, the second parameter will contain an
    associative array of options, and the third parameter will contain the
    value or values you wish to be selected.

    The parameter usage is identical to using :php:func:`form_dropdown()` above,
    except of course that the name of the field will need to use POST array
    syntax, e.g., foo[].

.. php:function:: form_fieldset([$legend_text = ''[, $attributes = []]])

    :param    string    $legend_text: Text to put in the <legend> tag
    :param    array    $attributes: Attributes to be set on the <fieldset> tag
    :returns:    An HTML fieldset opening tag
    :rtype:    string

    Lets you generate fieldset/legend fields.

    Example:

    .. literalinclude:: form_helper/021.php

    Similar to other functions, you can submit an associative array in the
    second parameter if you prefer to set additional attributes:

    .. literalinclude:: form_helper/022.php

.. php:function:: form_fieldset_close([$extra = ''])

    :param    string    $extra: Anything to append after the closing tag, *as is*
    :returns:    An HTML fieldset closing tag
    :rtype:    string

    Produces a closing ``</fieldset>`` tag. The only advantage to using this
    function is it permits you to pass data to it which will be added below
    the tag. For example

    .. literalinclude:: form_helper/023.php

.. php:function:: form_checkbox([$data = ''[, $value = ''[, $checked = false[, $extra = '']]]])

    :param    array    $data: Field attributes data
    :param    string    $value: Field value
    :param    bool    $checked: Whether to mark the checkbox as being *checked*
    :param    mixed    $extra: Extra attributes to be added to the tag either as an array or a literal string
    :returns:    An HTML checkbox input tag
    :rtype:    string

    Lets you generate a checkbox field. Simple example:

    .. literalinclude:: form_helper/024.php

    The third parameter contains a boolean true/false to determine whether
    the box should be checked or not.

    Similar to the other form functions in this helper, you can also pass an
    array of attributes to the function:

    .. literalinclude:: form_helper/025.php

    Also as with other functions, if you would like the tag to contain
    additional data like JavaScript, you can pass it as a string in the
    fourth parameter:

    .. literalinclude:: form_helper/026.php

    Or you can pass it as an array:

    .. literalinclude:: form_helper/027.php

.. php:function:: form_radio([$data = ''[, $value = ''[, $checked = false[, $extra = '']]]])

    :param    array    $data: Field attributes data
    :param    string    $value: Field value
    :param    bool    $checked: Whether to mark the radio button as being *checked*
    :param    mixed    $extra: Extra attributes to be added to the tag either as an array or a literal string
    :returns:    An HTML radio input tag
    :rtype:    string

    This function is identical in all respects to the :php:func:`form_checkbox()`
    function above except that it uses the "radio" input type.

.. php:function:: form_label([$label_text = ''[, $id = ''[, $attributes = []]]])

    :param    string    $label_text: Text to put in the <label> tag
    :param    string    $id: ID of the form element that we're making a label for
    :param    string    $attributes: HTML attributes
    :returns:    An HTML field label tag
    :rtype:    string

    Lets you generate a <label>. Simple example:

    .. literalinclude:: form_helper/028.php

    Similar to other functions, you can submit an associative array in the
    third parameter if you prefer to set additional attributes.

    Example:

    .. literalinclude:: form_helper/029.php

.. php:function:: form_submit([$data = ''[, $value = ''[, $extra = '']]])

    :param    string    $data: Button name
    :param    string    $value: Button value
    :param    mixed    $extra: Extra attributes to be added to the tag either as an array or a literal string
    :returns:    An HTML input submit tag
    :rtype:    string

    Lets you generate a standard submit button. Simple example:

    .. literalinclude:: form_helper/030.php

    Similar to other functions, you can submit an associative array in the
    first parameter if you prefer to set your own attributes. The third
    parameter lets you add extra data to your form, like JavaScript.

.. php:function:: form_reset([$data = ''[, $value = ''[, $extra = '']]])

    :param    string    $data: Button name
    :param    string    $value: Button value
    :param    mixed    $extra: Extra attributes to be added to the tag either as an array or a literal string
    :returns:    An HTML input reset button tag
    :rtype:    string

    Lets you generate a standard reset button. Use is identical to
    :func:`form_submit()`.

.. php:function:: form_button([$data = ''[, $content = ''[, $extra = '']]])

    :param    string    $data: Button name
    :param    string    $content: Button label
    :param    mixed    $extra: Extra attributes to be added to the tag either as an array or a literal string
    :returns:    An HTML button tag
    :rtype:    string

    Lets you generate a standard button element. You can minimally pass the
    button name and content in the first and second parameter:

    .. literalinclude:: form_helper/031.php

    Or you can pass an associative array containing any data you wish your
    form to contain:

    .. literalinclude:: form_helper/032.php

    If you would like your form to contain some additional data, like
    JavaScript, you can pass it as a string in the third parameter:

    .. literalinclude:: form_helper/033.php

.. php:function:: form_close([$extra = ''])

    :param    string    $extra: Anything to append after the closing tag, *as is*
    :returns:    An HTML form closing tag
    :rtype:    string

    Produces a closing ``</form>`` tag. The only advantage to using this
    function is it permits you to pass data to it which will be added below
    the tag. For example:

    .. literalinclude:: form_helper/034.php

.. php:function:: set_value($field[, $default = ''[, $html_escape = true]])

    :param    string    $field: Field name
    :param    string    $default: Default value
    :param  bool    $html_escape: Whether to turn off HTML escaping of the value
    :returns:    Field value
    :rtype:    string

    Permits you to set the value of an input form or textarea. You must
    supply the field name via the first parameter of the function. The
    second (optional) parameter allows you to set a default value for the
    form. The third (optional) parameter allows you to turn off HTML escaping
    of the value, in case you need to use this function in combination with
    i.e., :php:func:`form_input()` and avoid double-escaping.

    Example::

        <input type="text" name="quantity" value="<?= set_value('quantity', '0') ?>" size="50">

    The above form will show "0" when loaded for the first time.

.. php:function:: set_select($field[, $value = ''[, $default = false]])

    :param    string    $field: Field name
    :param    string    $value: Value to check for
    :param    string    $default: Whether the value is also a default one
    :returns:    'selected' attribute or an empty string
    :rtype:    string

    If you use a <select> menu, this function permits you to display the
    menu item that was selected.

    The first parameter must contain the name of the select menu, the second
    parameter must contain the value of each item, and the third (optional)
    parameter lets you set an item as the default (use boolean true/false).

    Example::

        <select name="myselect">
            <option value="one" <?= set_select('myselect', 'one', true) ?>>One</option>
            <option value="two" <?= set_select('myselect', 'two') ?>>Two</option>
            <option value="three" <?= set_select('myselect', 'three') ?>>Three</option>
        </select>

.. php:function:: set_checkbox($field[, $value = ''[, $default = false]])

    :param    string    $field: Field name
    :param    string    $value: Value to check for
    :param    string    $default: Whether the value is also a default one
    :returns:    'checked' attribute or an empty string
    :rtype:    string

    Permits you to display a checkbox in the state it was submitted.

    The first parameter must contain the name of the checkbox, the second
    parameter must contain its value, and the third (optional) parameter
    lets you set an item as the default (use boolean true/false).

    Example::

        <input type="checkbox" name="mycheck[]" value="1" <?= set_checkbox('mycheck', '1') ?>>
        <input type="checkbox" name="mycheck[]" value="2" <?= set_checkbox('mycheck', '2') ?>>

.. php:function:: set_radio($field[, $value = ''[, $default = false]])

    :param    string    $field: Field name
    :param    string    $value: Value to check for
    :param    string    $default: Whether the value is also a default one
    :returns:    'checked' attribute or an empty string
    :rtype:    string

    Permits you to display radio buttons in the state they were submitted.
    This function is identical to the :php:func:`set_checkbox()` function above.

    Example::

        <input type="radio" name="myradio" value="1" <?= set_radio('myradio', '1', true) ?>>
        <input type="radio" name="myradio" value="2" <?= set_radio('myradio', '2') ?>>

.. php:function:: validation_errors()

    .. versionadded:: 4.3.0

    :returns:   The validation errors
    :rtype:    array

    Returns the validation errors. First, this function checks the validation errors
    that are stored in the session. To store the errors in the session, you need to use ``withInput()`` with :php:func:`redirect() <redirect>`.

    The returned array is the same as ``Validation::getErrors()``.
    See :ref:`Validation <validation-redirect-and-validation-errors>` for details.

    .. note:: This function does not work with :ref:`in-model-validation`. If you
        want to get the validation errors in model validation, see
        :ref:`model-getting-validation-errors`.

    Example::

        <?php $errors = validation_errors(); ?>

.. php:function:: validation_list_errors($template = 'list')

    .. versionadded:: 4.3.0

    :param    string    $template: Validation template name
    :returns:    Rendered HTML of the validation errors
    :rtype:    string

    Returns the rendered HTML of the validation errors.

    The parameter ``$template`` is a Validation template name.
    See :ref:`validation-customizing-error-display` for details.

    This function uses :php:func:`validation_errors()` internally.

    .. note:: This function does not work with :ref:`in-model-validation`. If you
        want to get the validation errors in model validation, see
        :ref:`model-getting-validation-errors`.

    Example::

        <?= validation_list_errors() ?>

.. php:function:: validation_show_error($field, $template = 'single')

    .. versionadded:: 4.3.0

    :param    string    $field: Field name
    :param    string    $template: Validation template name
    :returns:    Rendered HTML of the validation error
    :rtype:    string

    Returns a single error for the specified field in formatted HTML.

    The parameter ``$template`` is a Validation template name.
    See :ref:`validation-customizing-error-display` for details.

    This function uses :php:func:`validation_errors()` internally.

    .. note:: This function does not work with :ref:`in-model-validation`. If you
        want to get the validation errors in model validation, see
        :ref:`model-getting-validation-errors`.

    Example::

        <?= validation_show_error('username') ?>
