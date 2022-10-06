.. _validation:

Validation
##########

CodeIgniter provides a comprehensive data validation class that
helps minimize the amount of code you'll write.

.. contents::
    :local:
    :depth: 2

Overview
********

Before explaining CodeIgniter's approach to data validation, let's
describe the ideal scenario:

#. A form is displayed.
#. You fill it in and submit it.
#. If you submitted something invalid, or perhaps missed a required
   item, the form is redisplayed containing your data along with an
   error message describing the problem.
#. This process continues until you have submitted a valid form.

On the receiving end, the script must:

#. Check for required data.
#. Verify that the data is of the correct type, and meets the correct
   criteria. For example, if a username is submitted it must be
   validated to contain only permitted characters. It must be of a
   minimum length, and not exceed a maximum length. The username can't
   be someone else's existing username, or perhaps even a reserved word.
   Etc.
#. Sanitize the data for security.
#. Pre-format the data if needed.
#. Prep the data for insertion in the database.

Although there is nothing terribly complex about the above process, it
usually requires a significant amount of code, and to display error
messages, various control structures are usually placed within the form
HTML. Form validation, while simple to create, is generally very messy
and tedious to implement.

Form Validation Tutorial
************************

What follows is a "hands on" tutorial for implementing CodeIgniter's Form
Validation.

In order to implement form validation you'll need three things:

#. A :doc:`View </outgoing/views>` file containing a form.
#. A View file containing a "success" message to be displayed upon
   successful submission.
#. A :doc:`controller </incoming/controllers>` method to receive and
   process the submitted data.

Let's create those three things, using a member sign-up form as the
example.

The Form
========

Using a text editor, create a form called **signup.php**. In it, place this
code and save it to your **app/Views/** folder::

    <html>
    <head>
        <title>My Form</title>
    </head>
    <body>

        <?= $validation->listErrors() ?>

        <?= form_open('form') ?>

            <h5>Username</h5>
            <input type="text" name="username" value="<?= set_value('username') ?>" size="50">

            <h5>Password</h5>
            <input type="text" name="password" value="<?= set_value('password') ?>" size="50">

            <h5>Password Confirm</h5>
            <input type="text" name="passconf" value="<?= set_value('passconf') ?>" size="50">

            <h5>Email Address</h5>
            <input type="text" name="email" value="<?= set_value('email') ?>" size="50">

            <div><input type="submit" value="Submit"></div>

        <?= form_close() ?>

    </body>
    </html>

The Success Page
================

Using a text editor, create a form called **success.php**. In it, place
this code and save it to your **app/Views/** folder::

    <html>
    <head>
        <title>My Form</title>
    </head>
    <body>

        <h3>Your form was successfully submitted!</h3>

        <p><?= anchor('form', 'Try it again!') ?></p>

    </body>
    </html>

The Controller
==============

Using a text editor, create a controller called **Form.php**. In it, place
this code and save it to your **app/Controllers/** folder:

.. literalinclude:: validation/001.php

The Routes
==========

Then add routes for the controller in **app/Config/Routes.php**:

.. literalinclude:: validation/039.php
   :lines: 2-

Try it!
=======

To try your form, visit your site using a URL similar to this one::

    example.com/index.php/form/

If you submit the form you should simply see the form reload. That's
because you haven't set up any validation rules in ``$this->validate()`` yet.

The ``validate()`` method is a method in the Controller. It uses
the **Validation class** inside. See :ref:`controllers-validating-data`.

.. note:: Since you haven't told the ``validate()`` method to validate anything
    yet, it **returns false** (boolean false) **by default**. The ``validate()``
    method only returns true if it has successfully applied your rules without
    any of them failing.

Explanation
===========

You'll notice several things about the above pages.

signup.php
----------

The form (**signup.php**) is a standard web form with a couple of exceptions:

#. It uses a :doc:`form helper </helpers/form_helper>` to create the form opening
   and closing. Technically, this
   isn't necessary. You could create the form using standard HTML.
   However, the benefit of using the helper is that it generates the
   action URL for you, based on the URL in your config file. This makes
   your application more portable in the event your URLs change.
#. At the top of the form you'll notice the following function call:
   ::

    <?= $validation->listErrors() ?>

   This function will return any error messages sent back by the
   validator. If there are no messages it returns an empty string.

Form.php
--------

The controller (**Form.php**) has one property: ``$helpers``.
It loads the form helper used by your view files.

The controller has one method: ``index()``. This method returns
the **signup** view to show the form when a non-POST request comes. Otherwise, it
uses the Controller-provided ``validate()`` method. It also runs the validation routine.
Based on whether the validation was successful it either presents the
form or the success page.

Add Validation Rules
====================

Then add validation rules in the controller (**Form.php**):

.. literalinclude:: validation/002.php
   :lines: 2-

If you submit the form you should see the success page or the form with error messages.

Config for Validation
*********************

.. _validation-traditional-and-strict-rules:

Traditional and Strict Rules
============================

CI4 has two kinds of Validation rule classes.
The default rule classes (**Traditional Rules**) have the namespace ``CodeIgniter\Validation``,
and the new classes (**Strict Rules**) have ``CodeIgniter\Validation\StrictRules``, which provide strict validation.

The **Traditional Rules** implicitly assume that string values are validated,
and the input value may be converted implicitly to a string value.
It works for most basic cases like validating POST data.

However, for example, if you use JSON input data, it may be a type of bool/null/array.
When you validate the boolean ``true``, it is converted to string ``'1'`` with the Traditional rule classes.
If you validate it with the ``integer`` rule, ``'1'`` passes the validation.

The **Strict Rules** don't use implicit type conversion.

.. warning:: When validating data that contains non-string values, such as JSON data, it is recommended to use **Strict Rules**.

Using Strict Rules
------------------

If you want to use these rules, you need to change the rule classes in **app/Config/Validation.php**:

.. literalinclude:: validation/003.php

Loading the Library
*******************

The library is loaded as a service named **validation**:

.. literalinclude:: validation/004.php

This automatically loads the ``Config\Validation`` file which contains settings
for including multiple Rulesets, and collections of rules that can be easily reused.

.. note:: You may never need to use this method, as both the :doc:`Controller </incoming/controllers>` and
    the :doc:`Model </models/model>` provide methods to make validation even easier.

Setting Validation Rules
************************

CodeIgniter lets you set as many validation rules as you need for a
given field, cascading them in order. To set validation rules you
will use the ``setRule()``, ``setRules()``, or ``withRequest()``
methods.

setRule()
=========

This method sets a single rule. It has the method signature::

    setRule(string $field, ?string $label, array|string $rules[, array $errors = []])

The ``$rules`` either takes in a pipe-delimited list of rules or an array collection of rules:

.. literalinclude:: validation/005.php

The value you pass to ``$field`` must match the key of any data array that is sent in. If
the data is taken directly from ``$_POST``, then it must be an exact match for
the form input name.

.. warning:: Prior to v4.2.0, this method's third parameter, ``$rules``, was typehinted to accept
    ``string``. In v4.2.0 and after, the typehint was removed to allow arrays, too. To avoid LSP being
    broken in extending classes overriding this method, the child class's method should also be modified
    to remove the typehint.

setRules()
==========

Like ``setRule()``, but accepts an array of field names and their rules:

.. literalinclude:: validation/006.php

To give a labeled error message you can set up as:

.. literalinclude:: validation/007.php

withRequest()
=============

One of the most common times you will use the validation library is when validating
data that was input from an HTTP Request. If desired, you can pass an instance of the
current Request object and it will take all of the input data and set it as the
data to be validated:

.. literalinclude:: validation/008.php

Working with Validation
***********************

Validating Keys that are Arrays
===============================

If your data is in a nested associative array, you can use "dot array syntax" to
easily validate your data:

.. literalinclude:: validation/009.php

You can use the '*' wildcard symbol to match any one level of the array:

.. literalinclude:: validation/010.php

"dot array syntax" can also be useful when you have single dimension array data.
For example, data returned by multi select dropdown:

.. literalinclude:: validation/011.php

Validate 1 Value
================

Validate one value against a rule:

.. literalinclude:: validation/012.php

Saving Sets of Validation Rules to the Config File
==================================================

A nice feature of the Validation class is that it permits you to store all
your validation rules for your entire application in a config file. You organize
the rules into "groups". You can specify a different group every time you run
the validation.

.. _validation-array:

How to save your rules
----------------------

To store your validation rules, simply create a new public property in the ``Config\Validation``
class with the name of your group. This element will hold an array with your validation
rules. As shown earlier, the validation array will have this prototype:

.. literalinclude:: validation/013.php

You can specify the group to use when you call the ``run()`` method:

.. literalinclude:: validation/014.php

You can also store custom error messages in this configuration file by naming the
property the same as the group, and appended with ``_errors``. These will automatically
be used for any errors when this group is used:

.. literalinclude:: validation/015.php

Or pass all settings in an array:

.. literalinclude:: validation/016.php

See below for details on the formatting of the array.

Getting & Setting Rule Groups
-----------------------------

**Get Rule Group**

This method gets a rule group from the validation configuration:

.. literalinclude:: validation/017.php

**Set Rule Group**

This method sets a rule group from the validation configuration to the validation service:

.. literalinclude:: validation/018.php

Running Multiple Validations
============================

.. note:: ``run()`` method will not reset error state. Should a previous run fail,
   ``run()`` will always return false and ``getErrors()`` will return
   all previous errors until explicitly reset.

If you intend to run multiple validations, for instance on different data sets or with different
rules after one another, you might need to call ``$validation->reset()`` before each run to get rid of
errors from previous run. Be aware that ``reset()`` will invalidate any data, rule or custom error
you previously set, so ``setRules()``, ``setRuleGroup()`` etc. need to be repeated:

.. literalinclude:: validation/019.php

Validation Placeholders
=======================

The Validation class provides a simple method to replace parts of your rules based on data that's being passed into it. This
sounds fairly obscure but can be especially handy with the ``is_unique`` validation rule. Placeholders are simply
the name of the field (or array key) that was passed in as ``$data`` surrounded by curly brackets. It will be
replaced by the **value** of the matched incoming field. An example should clarify this:

.. literalinclude:: validation/020.php

In this set of rules, it states that the email address should be unique in the database, except for the row
that has an id matching the placeholder's value. Assuming that the form POST data had the following:

.. literalinclude:: validation/021.php

then the ``{id}`` placeholder would be replaced with the number **4**, giving this revised rule:

.. literalinclude:: validation/022.php

So it will ignore the row in the database that has ``id=4`` when it verifies the email is unique.

This can also be used to create more dynamic rules at runtime, as long as you take care that any dynamic
keys passed in don't conflict with your form data.

Working With Errors
*******************

The Validation library provides several methods to help you set error messages, provide
custom error messages, and retrieve one or more errors to display.

By default, error messages are derived from language strings in ``system/Language/en/Validation.php``, where
each rule has an entry.

.. _validation-custom-errors:

Setting Custom Error Messages
=============================

Both the ``setRule()`` and ``setRules()`` methods can accept an array of custom messages
that will be used as errors specific to each field as their last parameter. This allows
for a very pleasant experience for the user since the errors are tailored to each
instance. If not custom error message is provided, the default value will be used.

These are two ways to provide custom error messages.

As the last parameter:

.. literalinclude:: validation/023.php

Or as a labeled style:

.. literalinclude:: validation/024.php

If you'd like to include a field's "human" name, or the optional parameter some rules allow for (such as max_length),
or the value that was validated you can add the ``{field}``, ``{param}`` and ``{value}`` tags to your message, respectively::

    'min_length' => 'Supplied value ({value}) for {field} must have at least {param} characters.'

On a field with the human name Username and a rule of ``min_length[6]`` with a value of "Pizza", an error would display: "Supplied value (Pizza) for Username must have
at least 6 characters."

.. warning:: If you get the error messages with ``getErrors()`` or ``getError()``, the messages are not HTML escaped. If you use user input data like ``({value})`` to make the error message, it might contain HTML tags. If you don't escape the messages before displying them, XSS attacks are possible.

.. note:: When using label-style error messages, if you pass the second parameter to ``setRules()``, it will be overwritten with the value of the first parameter.

Translation Of Messages And Validation Labels
=============================================

To use translated strings from language files, we can simply use the dot syntax.
Let's say we have a file with translations located here: ``app/Languages/en/Rules.php``.
We can simply use the language lines defined in this file, like this:

.. literalinclude:: validation/025.php

.. _validation-getting-all-errors:

Getting All Errors
==================

If you need to retrieve all error messages for failed fields, you can use the ``getErrors()`` method:

.. literalinclude:: validation/026.php

If no errors exist, an empty array will be returned.

When using a wildcard, the error will point to a specific field, replacing the asterisk with the appropriate key/keys::

    // for data
    'contacts' => [
        'friends' => [
            [
                'name' => 'Fred Flinstone',
            ],
            [
                'name' => '',
            ],
        ]
    ]

    // rule
    'contacts.*.name' => 'required'

    // error will be
    'contacts.friends.1.name' => 'The contacts.*.name field is required.'

Getting a Single Error
======================

You can retrieve the error for a single field with the ``getError()`` method. The only parameter is the field
name:

.. literalinclude:: validation/027.php

If no error exists, an empty string will be returned.

.. note:: When using a wildcard, all found errors that match the mask will be combined into one line separated by the EOL character.

Check If Error Exists
=====================

You can check to see if an error exists with the ``hasError()`` method. The only parameter is the field name:

.. literalinclude:: validation/028.php

When specifying a field with a wildcard, all errors matching the mask will be checked:

.. literalinclude:: validation/029.php

Customizing Error Display
*************************

When you call ``$validation->listErrors()`` or ``$validation->showError()``, it loads a view file in the background
that determines how the errors are displayed. By default, they display with a class of ``errors`` on the wrapping div.
You can easily create new views and use them throughout your application.

Creating the Views
==================

The first step is to create custom views. These can be placed anywhere that the ``view()`` method can locate them,
which means the standard View directory, or any namespaced View folder will work. For example, you could create
a new view at **app/Views/_errors_list.php**:

.. literalinclude:: validation/030.php

An array named ``$errors`` is available within the view that contains a list of the errors, where the key is
the name of the field that had the error, and the value is the error message, like this:

.. literalinclude:: validation/031.php

There are actually two types of views that you can create. The first has an array of all of the errors, and is what
we just looked at. The other type is simpler, and only contains a single variable, ``$error`` that contains the
error message. This is used with the ``showError()`` method where a field must be specified::

    <span class="help-block"><?= esc($error) ?></span>

Configuration
=============

Once you have your views created, you need to let the Validation library know about them. Open ``Config/Validation.php``.
Inside, you'll find the ``$templates`` property where you can list as many custom views as you want, and provide an
short alias they can be referenced by. If we were to add our example file from above, it would look something like:

.. literalinclude:: validation/032.php

Specifying the Template
=======================

You can specify the template to use by passing it's alias as the first parameter in ``listErrors()``::

    <?= $validation->listErrors('my_list') ?>

When showing field-specific errors, you can pass the alias as the second parameter to the ``showError()`` method,
right after the name of the field the error should belong to::

    <?= $validation->showError('username', 'my_single') ?>

Creating Custom Rules
*********************

Rules are stored within simple, namespaced classes. They can be stored any location you would like, as long as the
autoloader can find it. These files are called RuleSets. To add a new RuleSet, edit **Config/Validation.php** and
add the new file to the ``$ruleSets`` array:

.. literalinclude:: validation/033.php

You can add it as either a simple string with the fully qualified class name, or using the ``::class`` suffix as
shown above. The primary benefit here is that it provides some extra navigation capabilities in more advanced IDEs.

Within the file itself, each method is a rule and must accept a string as the first parameter, and must return
a boolean true or false value signifying true if it passed the test or false if it did not:

.. literalinclude:: validation/034.php

By default, the system will look within ``CodeIgniter\Language\en\Validation.php`` for the language strings used
within errors. In custom rules, you may provide error messages by accepting a ``$error`` variable by reference in the
second parameter:

.. literalinclude:: validation/035.php

Your new custom rule could now be used just like any other rule:

.. literalinclude:: validation/036.php

Allowing Parameters
===================

If your method needs to work with parameters, the function will need a minimum of three parameters: the string to validate,
the parameter string, and an array with all of the data that was submitted the form. The ``$data`` array is especially handy
for rules like ``required_with`` that needs to check the value of another submitted field to base its result on:

.. literalinclude:: validation/037.php

Custom errors can be returned as the fourth parameter, just as described above.

Available Rules
***************

.. note:: Rule is a string; there must be **no spaces** between the parameters, especially the ``is_unique`` rule.
    There can be no spaces before and after ``ignore_value``.

.. literalinclude:: validation/038.php

Rules for General Use
=====================

The following is a list of all the native rules that are available to use:

======================= ========== ============================================= ===================================================
Rule                    Parameter  Description                                   Example
======================= ========== ============================================= ===================================================
alpha                   No         Fails if field has anything other than
                                   alphabetic characters.
alpha_space             No         Fails if field contains anything other than
                                   alphabetic characters or spaces.
alpha_dash              No         Fails if field contains anything other than
                                   alphanumeric characters, underscores or
                                   dashes.
alpha_numeric           No         Fails if field contains anything other than
                                   alphanumeric characters.
alpha_numeric_space     No         Fails if field contains anything other than
                                   alphanumeric or space characters.
alpha_numeric_punct     No         Fails if field contains anything other than
                                   alphanumeric, space, or this limited set of
                                   punctuation characters: ~ (tilde),
                                   ! (exclamation), # (number), $ (dollar),
                                   % (percent), & (ampersand), * (asterisk),
                                   - (dash), _ (underscore), + (plus),
                                   = (equals), | (vertical bar), : (colon),
                                   . (period).
decimal                 No         Fails if field contains anything other than
                                   a decimal number.
                                   Also accepts a + or  - sign for the number.
differs                 Yes        Fails if field does not differ from the one   differs[field_name]
                                   in the parameter.
exact_length            Yes        Fails if field is not exactly the parameter   exact_length[5] or exact_length[5,8,12]
                                   value. One or more comma-separated values.
greater_than            Yes        Fails if field is less than or equal to       greater_than[8]
                                   the parameter value or not numeric.
greater_than_equal_to   Yes        Fails if field is less than the parameter     greater_than_equal_to[5]
                                   value, or not numeric.
hex                     No         Fails if field contains anything other than
                                   hexadecimal characters.
if_exist                No         If this rule is present, validation will
                                   only return possible errors if the field key
                                   exists, regardless of its value.
in_list                 Yes        Fails if field is not within a predetermined  in_list[red,blue,green]
                                   list.
integer                 No         Fails if field contains anything other than
                                   an integer.
is_natural              No         Fails if field contains anything other than
                                   a natural number: 0, 1, 2, 3, etc.
is_natural_no_zero      No         Fails if field contains anything other than
                                   a natural number, except zero: 1, 2, 3, etc.
is_not_unique           Yes        Checks the database to see if the given value is_not_unique[table.field,where_field,where_value]
                                   exist. Can ignore records by field/value to
                                   filter (currently accept only one filter).
is_unique               Yes        Checks if this field value exists in the      is_unique[table.field,ignore_field,ignore_value]
                                   database. Optionally set a column and value
                                   value to ignore, useful when updating records
                                   to ignore itself.
less_than               Yes        Fails if field is greater than or equal to    less_than[8]
                                   the parameter value or not numeric.
less_than_equal_to      Yes        Fails if field is greater than the parameter  less_than_equal_to[8]
                                   value or not numeric.
matches                 Yes        The value must match the value of the field
                                   in the parameter.                             matches[field]
max_length              Yes        Fails if field is longer than the parameter   max_length[8]
                                   value.
min_length              Yes        Fails if field is shorter than the parameter  min_length[3]
                                   value.
not_in_list             Yes        Fails if field is within a predetermined      not_in_list[red,blue,green]
                                   list.
numeric                 No         Fails if field contains anything other than
                                   numeric characters.
regex_match             Yes        Fails if field does not match the regular     regex_match[/regex/]
                                   expression.
permit_empty            No         Allows the field to receive an empty array,
                                   empty string, null or false.
required                No         Fails if the field is an empty array, empty
                                   string, null or false.
required_with           Yes        The field is required when any of the other   required_with[field1,field2]
                                   required fields are present in the data.
required_without        Yes        The field is required when any of other       required_without[field1,field2]
                                   fields do not pass ``required`` checks.
string                  No         A generic alternative to the alpha* rules
                                   that confirms the element is a string
timezone                No         Fails if field does match a timezone per
                                   ``timezone_identifiers_list``
valid_base64            No         Fails if field contains anything other than
                                   valid Base64 characters.
valid_json              No         Fails if field does not contain a valid JSON
                                   string.
valid_email             No         Fails if field does not contain a valid
                                   email address.
valid_emails            No         Fails if any value provided in a comma
                                   separated list is not a valid email.
valid_ip                No         Fails if the supplied IP is not valid.        valid_ip[ipv6]
                                   Accepts an optional parameter of 'ipv4' or
                                   'ipv6' to specify an IP format.
valid_url               No         Fails if field does not contain (loosely) a
                                   URL. Includes simple strings that could be
                                   hostnames, like "codeigniter".
valid_url_strict        Yes        Fails if field does not contain a valid URL.  valid_url_strict[https]
                                   You can optionally specify a list of valid
                                   schemas. If not specified, ``http,https``
                                   are valid. This rule uses
                                   PHP's ``FILTER_VALIDATE_URL``.
valid_date              No         Fails if field does not contain a valid date. valid_date[d/m/Y]
                                   Accepts an optional parameter to matches
                                   a date format.
valid_cc_number         Yes        Verifies that the credit card number matches  valid_cc_number[amex]
                                   the format used by the specified provider.
                                   Current supported providers are:
                                   American Express (amex),
                                   China Unionpay (unionpay),
                                   Diners Club CarteBlance (carteblanche),
                                   Diners Club (dinersclub),
                                   Discover Card (discover),
                                   Interpayment (interpayment), JCB (jcb),
                                   Maestro (maestro), Dankort (dankort),
                                   NSPK MIR (mir),
                                   Troy (troy), MasterCard (mastercard),
                                   Visa (visa), UATP (uatp), Verve (verve),
                                   CIBC Convenience Card (cibc),
                                   Royal Bank of Canada Client Card (rbc),
                                   TD Canada Trust Access Card (tdtrust),
                                   Scotiabank Scotia Card (scotia),
                                   BMO ABM Card (bmoabm),
                                   HSBC Canada Card (hsbc)
======================= ========== ============================================= ===================================================

.. note:: You can also use any native PHP functions that return boolean and
    permit at least one parameter, the field data to validate.
    The Validation library **never alters the data** to validate.

.. _rules-for-file-uploads:

Rules for File Uploads
======================

These validation rules enable you to do the basic checks you might need to verify that uploaded files meet your business needs.
Since the value of a file upload HTML field doesn't exist, and is stored in the ``$_FILES`` global, the name of the input field will
need to be used twice. Once to specify the field name as you would for any other rule, but again as the first parameter of all
file upload related rules::

    // In the HTML
    <input type="file" name="avatar">

    // In the controller
    $this->validate([
        'avatar' => 'uploaded[avatar]|max_size[avatar,1024]',
    ]);

======================= ========== ============================================= ===================================================
Rule                    Parameter  Description                                   Example
======================= ========== ============================================= ===================================================
uploaded                Yes         Fails if the name of the parameter does not  uploaded[field_name]
                                    match the name of any uploaded files.
max_size                Yes         Fails if the uploaded file named in the      max_size[field_name,2048]
                                    parameter is larger than the second
                                    parameter in kilobytes (kb). Or if the file
                                    is larger than allowed maximum size declared
                                    in php.ini config file -
                                    ``upload_max_filesize`` directive.
max_dims                Yes         Fails if the maximum width and height of an  max_dims[field_name,300,150]
                                    uploaded image exceed values. The first
                                    parameter is the field name. The second is
                                    the width, and the third is the height. Will
                                    also fail if the file cannot be determined
                                    to be an image.
mime_in                 Yes         Fails if the file's mime type is not one     mime_in[field_name,image/png,image/jpeg]
                                    listed in the parameters.
ext_in                  Yes         Fails if the file's extension is not one     ext_in[field_name,png,jpg,gif]
                                    listed in the parameters.
is_image                Yes         Fails if the file cannot be determined to be is_image[field_name]
                                    an image based on the mime type.
======================= ========== ============================================= ===================================================

The file validation rules apply for both single and multiple file uploads.
