##########
Validation
##########

CodeIgniter provides a comprehensive data validation class that
helps minimize the amount of code you'll write.

.. contents:: Page Contents

********
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
#. Pre-format the data if needed (Does the data need to be trimmed? HTML
   encoded? Etc.)
#. Prep the data for insertion in the database.

Although there is nothing terribly complex about the above process, it
usually requires a significant amount of code, and to display error
messages, various control structures are usually placed within the form
HTML. Form validation, while simple to create, is generally very messy
and tedious to implement.

************************
Form Validation Tutorial
************************

What follows is a "hands on" tutorial for implementing CodeIgniter's Form
Validation.

In order to implement form validation you'll need three things:

#. A :doc:`View <../general/views>` file containing a form.
#. A View file containing a "success" message to be displayed upon
   successful submission.
#. A :doc:`controller <../general/controllers>` method to receive and
   process the submitted data.

Let's create those three things, using a member sign-up form as the
example.

The Form
========

Using a text editor, create a form called **Signup.php**. In it, place this
code and save it to your **application/Views/** folder::

	<html>
	<head>
	    <title>My Form</title>
	</head>
	<body>

        <?= $validation->listErrors() ?>

        <?= form_open('form') ?>

        <h5>Username</h5>
        <input type="text" name="username" value="" size="50" />

        <h5>Password</h5>
        <input type="text" name="password" value="" size="50" />

        <h5>Password Confirm</h5>
        <input type="text" name="passconf" value="" size="50" />

        <h5>Email Address</h5>
        <input type="text" name="email" value="" size="50" />

        <div><input type="submit" value="Submit" /></div>

        </form>

	</body>
	</html>

The Success Page
================

Using a text editor, create a form called **Success.php**. In it, place
this code and save it to your **application/Views/** folder::

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
this code and save it to your **application/Controllers/** folder::

	<?php namespace App\Controllers;

    use CodeIgniter\Controller;

	class Form extends Controller
	{
		public function index()
		{
		    helper(['form', 'url']);

            if (! $this->validate($this->request, []))
			{
				echo view('Signup', [
				    'validation' => $this->validation
				]);
			}
			else
			{
				echo view('Success');
			}
		}
	}

Try it!
=======

To try your form, visit your site using a URL similar to this one::

	example.com/index.php/form/

If you submit the form you should simply see the form reload. That's
because you haven't set up any validation rules yet.

**Since you haven't told the Validation class to validate anything
yet, it returns false (boolean false) by default. The ``run()`` method
only returns true if it has successfully applied your rules without any
of them failing.**

Explanation
===========

You'll notice several things about the above pages:

The form (Signup.php) is a standard web form with a couple exceptions:

#. It uses a form helper to create the form opening. Technically, this
   isn't necessary. You could create the form using standard HTML.
   However, the benefit of using the helper is that it generates the
   action URL for you, based on the URL in your config file. This makes
   your application more portable in the event your URLs change.
#. At the top of the form you'll notice the following function call:
   ::

	<?= validation_errors() ?>

   This function will return any error messages sent back by the
   validator. If there are no messages it returns an empty string.

The controller (Form.php) has one method: ``index()``. This method
initializes the validation class and loads the form helper and URL
helper used by your view files. It also runs the validation routine.
Based on whether the validation was successful it either presents the
form or the success page.







Loading the Library
===================

The library is loaded as a service named **validation**::

    $validation =  Config\Services::validation();

This automatically loads the ``Config\Validation`` file which contains settings
for including multiple Rule sets, and collections of rules that can be easily reused.

.. note:: You may never need to use this method, as both the :doc:`Controller </general/controllers>` and
    the :doc:`Model </database/model>` provide methods to make validation even easier.

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

.. _validation-array:

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

.. _validation-custom-errors:

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

======================= =========== =============================================================================================== ===================================================
Rule                    Parameter   Description                                                                                     Example
======================= =========== =============================================================================================== ===================================================
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
valid_cc_number         Yes         Verifies that the credit card number matches the format used by the specified provider.         valid_cc_number[amex]
                                    Current supported providers are: American Express (amex), China Unionpay (unionpay),
                                    Diners Club CarteBlance (carteblanche), Diners Club (dinersclub), Discover Card (discover),
                                    Interpayment (interpayment), JCB (jcb), Maestro (maestro), Dankort (dankort), NSPK MIR (mir),
                                    MasterCard (mastercard), Visa (visa), UATP (uatp), Verve (verve),
                                    CIBC Convenience Card (cibc), Royal Bank of Canada Client Card (rbc),
                                    TD Canada Trust Access Card (tdtrust), Scotiabank Scotia Card (scotia), BMO ABM Card (bmoabm),
                                    HSBC Canada Card (hsbc)
======================= =========== =============================================================================================== ===================================================

Rules for File Uploads
======================

These validation rules enable you to do the basic checks you might need to verify that uploaded files meet your business needs.
Since the value of a file upload HTML field doesn't exist, and is stored in the $_FILES global, the name of the input field will
need to be used twice. Once to specify the field name as you would for any other rule, but again as the first parameter of all
file upload related rules::

    // In the HTML
    <input type="file" name="avatar">

    // In the controller
    $this->validate($request, [
        'avatar' => 'uploaded[avatar]|max_size[avatar,1024]'
    ]);

======================= =========== =============================================================================================== ========================================
Rule                    Parameter   Description                                                                                     Example
======================= =========== =============================================================================================== ========================================
uploaded                Yes         Fails if the name of the parameter does not match the name of any uploaded files.               uploaded[field_name]
max_size                Yes         Fails if the uploaded file named in the parameter is larger than the second parameter in        max_size[field_name,2048]
                                    kilobytes (kb).
max_dims                Yes         Files if the maximum width and height of an uploaded image exceeds values. The first parameter  max_dims[field_name,300,150]
                                    is the field name. The second is the width, and the third is the height. Will also fail if
                                    the file cannot be determined to be an image.
mime_in                 Yes         Fails if the file's mime type is not one listed in the parameter.                               mime_in[field_name,image/png,image/jpg]
ext_in                  Yes         Fails if the file's extension is not one listed in the parameter.                               ext_in[field_name,png,jpg,gif]
is_image                Yes         Fails if the file cannot be determined to be an image based on the mime type.                   is_image[field_name]
======================= =========== =============================================================================================== ========================================


.. note:: You can also use any native PHP functions that permit up
	to two parameters, where at least one is required (to pass
	the field data).