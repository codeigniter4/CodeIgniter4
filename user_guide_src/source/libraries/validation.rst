##########
Validation
##########

CodeIgniter provides a comprehensive data validation class that
helps minimize the amount of code you'll write.

.. contents:: Page Contents

********
Overview
********

Loading the Library
===================

The library is loaded as a service named **validation**::

    $validation =  Config\Services::validation();

This automatically loads the ``Config\Validation`` file which contains settings
for including multiple Rule sets, and collections of rules that can be easily reused.

Setting Validation Rules
========================

CodeIgniter lets you set as many validation rules as you need for a
given field, cascading them in order. To set validation rules you
will use the ``setRule()``, ``setRules()``, or ``withRequest()``
methods.

setRule()
---------

This method sets a single rule. It takes the name of field as
the first parameter, and a string with a pipe-delimited list of rules
that should be applied::

    $validation->setRule('username', 'required');

The **field name** must match the key of any data array that is sent in. If
the data is taken directly from $_POST, then it must be an exact match for
the form input name.

setRules()
----------

Like, ``setRule()``, but accepts an array of field names and their rules::

    $validation->setRules([
        'username' => 'required',
        'password' => 'required|min_length[10]'
    ]);

withRequest()
-------------

One of the most common times you will use the validation library is when validating
data that was input from an HTML form. If desired, you can pass an instance of the
current Request object and it will take all of the $_POST data and set it as the
data to be validated::

    $validation->withRequest($this->request)
               ->run();


**************************************************
Saving Sets of Validation Rules to the Config File
**************************************************

A nice feature of the Validation class is that it permits you to store all
your validation rules for your entire application in a config file. You organize
the rules into "groups". You can specify a different group every time you run
the validation.

How to save your rules
======================

To store your validation rules, simply create a new public property in the ``Config\Validation``
class with the name of your group. This element will hold an array with your validation
rules. As shown earlier, the validation array will have this prototype::

    class Validation
    {
        public $signup = [
            'username'     => 'required',
            'password'     => 'required',
            'pass_confirm' => 'required|matches[password]',
            'email'        => 'required|valid_email'
        ];
    }

You can specify the group to use when you call the ``run()`` method::

    $validation->run($data, $signup);

You can also store custom error messages in this configuration file by naming the
property the same as the group, and appended with ``_errors``. These will automatically
be used for any errors when this group is used::

    class Validation
    {
        public $signup = [
            'username'     => 'required',
            'password'     => 'required',
            'pass_confirm' => 'required|matches[password]',
            'email'        => 'required|valid_email'
        ];

        public $signup_errors = [
            'username' => [
                'required' => 'You must choose a username.',
            ],
            'email' => [
                'valid_email' => 'Please check the Email field. It does not appear to be valid.'
            ]
        ]
    }

See below for details on the formatting of the array.

*******************
Working With Errors
*******************

The Validation library provides several methods to help you set error messages, provide
custom error messages, and retrieve one or more errors to display.

By default, error messages are derived from language strings in ``system/Language/en/Validation.php``, where
each rule has an entry.

**TODO: Determine how to easily add custom rule messages.**

Setting Custom Error Messages
=============================

Both the ``setRule()`` and ``setRules()`` methods can accept an array of custom messages
that will be used as errors specific to each field as their last parameter. This allows
for a very pleasant experience for the user since the errors are tailored to the each
instance. If not custom error message is provided, the default value will be used.

The array is structured as follows::

    [
        'field' => [
            'rule' => 'message',
            'rule' => 'message
        ],
    ]

Here is a more practical example::

    $rules = [
        'username' => [
            'required' => 'All accounts must have usernames provided',
        ],
        'password' => [
            'min_length' => 'Your password is too short. You want to get hacked?'
        ]
    ];

    $validation->setRules([
            'username' => 'required|is_unique[users.username]',
            'password' => 'required|min_length[10]'
        ],
        $rules
    );

Getting All Errors
==================

If you need to retrieve all error messages for failed fields, you can use the ``getErrors()`` method::

    $errors = $validation->getErrors();

    // Returns:
    [
        'field1' => 'error message',
        'field2' => 'error message',
    ]

If no errors exist, an empty array will be returned.

Getting a Single Error
======================

You can retrieve the error for a single field with the ``getError()`` method. The only parameter is the field
name::

    $error = $validation->getError('username');

If no error exists, an empty string will be returned.

Check If Error Exists
=====================

You can check to see if an error exists with the ``hasError()`` method. The only parameter is the field name::

    if ($validation->hasError('username')
    {
        echo $validation->getError('username');
    }




***************
Available Rules
***************

The following is a list of all the native rules that are available to use:

======================= =========== =============================================================================================== ====================================
Rule                    Parameter   Description                                                                                     Example
======================= =========== =============================================================================================== ====================================
alpha                   No          Fails if field has anything other than alphabetic characters.
alpha_dash              No          Fails if field contains anything other than alpha-numeric characters, underscores or dashes.
alpha_numeric           No          Fails if field contains anything other than alpha-numeric characters or numbers.
alpha_numeric_space     No          Fails if field contains anything other than alpha-numeric characters, numbers or space.
decimal                 No          Fails if field contains anything other than a decimal number.
differs                 Yes         Fails if field does not differ from the one in the parameter.                                   differs[field_name]
exact_length            Yes         Fails if field is not exactly the parameter value.                                              exact_length[5]
greater_than            Yes         Fails if field is less than or equal to the parameter value or not numeric.                     greater_than[8]
greater_than_equal_to   Yes         Fails if field is less than the parameter value, or not numeric.                                greater_than_equal_to[5]
in_list                 Yes         Fails if field is not within a predetermined list.                                              in_list[red,blue,green]
integer                 No          Fails if field contains anything other than an integer.
is_natural              No          Fails if field contains anything other than a natural number: 0, 1, 2, 3, etc.
is_natural_no_zero      No          Fails if field contains anything other than a natural number, except zero: 1, 2, 3, etc.
less_than               Yes         Fails if field is greater than or equal to the parameter value or not numeric.                  less_than[8]
less_then_equal_to      Yes         Fails if field is greater than the parameter value or not numeric.                              less_than_equal_to[8]
matches                 Yes         The value must match the value of the field in the parameter.                                   matches[field]
max_length              Yes         Fails if field is longer than the parameter value.                                              max_length[8]
min_length              Yes         Fails if field is shorter than the parameter value.                                             min_length[3]
numeric                 No          Fails if field contains anything other than numeric characters.
regex_match             Yes         Fails if field does not match the regular expression.                                           regex_match[/regex/]
required                No          Fails if the field is empty.
required_with           Yes         The field is required if any of the fields in the parameter are set.                            required_with[field1,field2]
required_without        Yes         The field is required when any of the fields in the parameter are not set.                      required_without[field1,field2]
is_unique               Yes         Checks if this field value exists in the database. Optionally set a                             is_unique[table.field,ignore_field,ignore_value]
                                    column and value to ignore, useful when updating records to ignore itself.
timezone                No          Fails if field does match a timezone per ``timezone_identifiers_list``
valid_base64            No          Fails if field contains anything other than valid Base64 characters.
valid_email             No          Fails if field does not contain a valid email address.
valid_emails            No          Fails if any value provided in a comma separated list is not a valid email.
valid_ip                No          Fails if the supplied IP is not valid. Accepts an optional parameter of ‘ipv4’ or               valid_ip[ipv6]
                                    ‘ipv6’ to specify an IP format.
valid_url               No          Fails if field does not contain a valid URL.
======================= =========== =============================================================================================== ====================================


.. note:: You can also use any native PHP functions that permit up
	to two parameters, where at least one is required (to pass
	the field data).