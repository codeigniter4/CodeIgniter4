Validation
##################################################

CodeIgniter provides a comprehensive data validation class that
helps minimize the amount of code you'll write.

.. contents::
    :local:
    :depth: 2

Overview
************************************************

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

Form Validation Tutorial
************************************************

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
================================================

Using a text editor, create a form called **Signup.php**. In it, place this
code and save it to your **app/Views/** folder::

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
================================================

Using a text editor, create a form called **Success.php**. In it, place
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
================================================

Using a text editor, create a controller called **Form.php**. In it, place
this code and save it to your **app/Controllers/** folder::

	<?php namespace App\Controllers;

	use CodeIgniter\Controller;

	class Form extends Controller
	{
		public function index()
		{
			helper(['form', 'url']);

			if (! $this->validate([]))
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
================================================

To try your form, visit your site using a URL similar to this one::

	example.com/index.php/form/

If you submit the form you should simply see the form reload. That's
because you haven't set up any validation rules yet.

**Since you haven't told the Validation class to validate anything
yet, it returns false (boolean false) by default. The** ``validate()`` **method
only returns true if it has successfully applied your rules without any
of them failing.**

Explanation
================================================

You'll notice several things about the above pages:

The form (Signup.php) is a standard web form with a couple of exceptions:

#. It uses a form helper to create the form opening. Technically, this
   isn't necessary. You could create the form using standard HTML.
   However, the benefit of using the helper is that it generates the
   action URL for you, based on the URL in your config file. This makes
   your application more portable in the event your URLs change.
#. At the top of the form you'll notice the following function call:
   ::

	<?= $validation->listErrors() ?>

   This function will return any error messages sent back by the
   validator. If there are no messages it returns an empty string.

The controller (Form.php) has one method: ``index()``. This method
uses the Controller-provided validate method and loads the form helper and URL
helper used by your view files. It also runs the validation routine.
Based on whether the validation was successful it either presents the
form or the success page.

Loading the Library
================================================

The library is loaded as a service named **validation**::

    $validation =  \Config\Services::validation();

This automatically loads the ``Config\Validation`` file which contains settings
for including multiple Rulesets, and collections of rules that can be easily reused.

.. note:: You may never need to use this method, as both the :doc:`Controller </incoming/controllers>` and
    the :doc:`Model </models/model>` provide methods to make validation even easier.

Setting Validation Rules
================================================

CodeIgniter lets you set as many validation rules as you need for a
given field, cascading them in order. To set validation rules you
will use the ``setRule()``, ``setRules()``, or ``withRequest()``
methods.

setRule()
---------

This method sets a single rule. It takes the name of the field as
the first parameter, an optional label and a string with a pipe-delimited list of rules
that should be applied::

    $validation->setRule('username', 'Username', 'required');

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

To give a labeled error message you can setup as::

    $validation->setRules([
        'username' => ['label' => 'Username', 'rules' => 'required'],
        'password' => ['label' => 'Password', 'rules' => 'required|min_length[10]']
    ]);

withRequest()
-------------

One of the most common times you will use the validation library is when validating
data that was input from an HTTP Request. If desired, you can pass an instance of the
current Request object and it will take all of the input data and set it as the
data to be validated::

    $validation->withRequest($this->request)
               ->run();

Working with Validation
************************************************

Validating Keys that are Arrays
================================================

If your data is in a nested associative array, you can use "dot array syntax" to
easily validate your data::

    // The data to test:
    'contacts' => [
        'name' => 'Joe Smith',
        'friends' => [
            [
                'name' => 'Fred Flinstone'
            ],
            [
                'name' => 'Wilma'
            ]
        ]
    ]

    // Joe Smith
    $validation->setRules([
        'contacts.name' => 'required'
    ]);

    // Fred Flintsone & Wilma
    $validation->setRules([
        'contacts.friends.name' => 'required'
    ]);

You can use the '*' wildcard symbol to match any one level of the array::

    // Fred Flintsone & Wilma
    $validation->setRules([
        'contacts.*.name' => 'required'
    ]);

Validate 1 Value
================================================

Validate one value against a rule::

    $validation->check($value, 'required');

Saving Sets of Validation Rules to the Config File
=======================================================

A nice feature of the Validation class is that it permits you to store all
your validation rules for your entire application in a config file. You organize
the rules into "groups". You can specify a different group every time you run
the validation.

.. _validation-array:

How to save your rules
-------------------------------------------------------

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

    $validation->run($data, 'signup');

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
                'required'    => 'You must choose a username.',
            ],
            'email'    => [
                'valid_email' => 'Please check the Email field. It does not appear to be valid.'
            ]
        ];
    }

Or pass all settings in an array::

    class Validation
    {
        public $signup = [
            'username' => [
                'label'  => 'Username',
                'rules'  => 'required',
                'errors' => [
                    'required' => 'You must choose a {field}.'
                ]
            ],
            'email'    => 'required|valid_email'
        ];

        public $signup_errors = [
            'email' => [
                'valid_email' => 'Please check the Email field. It does not appear to be valid.'
            ]
        ];
    }

See below for details on the formatting of the array.

Getting & Setting Rule Groups
-------------------------------------------------------

**Get Rule Group**

This method gets a rule group from the validation configuration::

    $validation->getRuleGroup('signup');

**Set Rule Group**

This method sets a rule group from the validation configuration to the validation service::

    $validation->setRuleGroup('signup');

Working With Errors
************************************************

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

As the last parameter::

    $validation->setRules([
            'username' => 'required|is_unique[users.username]',
            'password' => 'required|min_length[10]'
        ],
        [   // Errors
            'username' => [
                'required' => 'All accounts must have usernames provided',
            ],
            'password' => [
                'min_length' => 'Your password is too short. You want to get hacked?'
            ]
        ]
    );

Or as a labeled style::

    $validation->setRules([
            'username' => [
                'label'  => 'Username',
                'rules'  => 'required|is_unique[users.username]',
                'errors' => [
                    'required' => 'All accounts must have {field} provided'
                ]
            ],
            'password' => [
                'label'  => 'Password',
                'rules'  => 'required|min_length[10]',
                'errors' => [
                    'min_length' => 'Your {field} is too short. You want to get hacked?'
                ]
            ]
        ]
    );

If you’d like to include a field’s “human” name, or the optional parameter some rules allow for (such as max_length),
you can add the ``{field}`` and ``{param}`` tags to your message, respectively::

    'min_length' => '{field} must have at least {param} characters.'

On a field with the human name Username and a rule of min_length[5], an error would display: “Username must have
at least 5 characters.”

.. note:: If you pass the last parameter the labeled style error messages will be ignored.

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

Customizing Error Display
************************************************

When you call ``$validation->listErrors()`` or ``$validation->showError()``, it loads a view file in the background
that determines how the errors are displayed. By default, they display with a class of ``errors`` on the wrapping div.
You can easily create new views and use them throughout your application.

Creating the Views
==================

The first step is to create custom views. These can be placed anywhere that the ``view()`` method can locate them,
which means the standard View directory, or any namespaced View folder will work. For example, you could create
a new view at **/app/Views/_errors_list.php**::

    <div class="alert alert-danger" role="alert">
        <ul>
        <?php foreach ($errors as $error) : ?>
            <li><?= esc($error) ?></li>
        <?php endforeach ?>
        </ul>
    </div>

An array named ``$errors`` is available within the view that contains a list of the errors, where the key is
the name of the field that had the error, and the value is the error message, like this::

    $errors = [
        'username' => 'The username field must be unique.',
        'email'    => 'You must provide a valid email address.'
    ];

There are actually two types of views that you can create. The first has an array of all of the errors, and is what
we just looked at. The other type is simpler, and only contains a single variable, ``$error`` that contains the
error message. This is used with the ``showError()`` method where a field must be specified::

    <span class="help-block"><?= esc($error) ?></span>

Configuration
=============

Once you have your views created, you need to let the Validation library know about them. Open ``Config/Validation.php``.
Inside, you'll find the ``$templates`` property where you can list as many custom views as you want, and provide an
short alias they can be referenced by. If we were to add our example file from above, it would look something like::

    public $templates = [
        'list'    => 'CodeIgniter\Validation\Views\list',
        'single'  => 'CodeIgniter\Validation\Views\single',
        'my_list' => '_errors_list'
    ];

Specifying the Template
=======================

You can specify the template to use by passing it's alias as the first parameter in ``listErrors``::

    <?= $validation->listErrors('my_list') ?>

When showing field-specific errors, you can pass the alias as the second parameter to the ``showError`` method,
right after the name of the field the error should belong to::

    <?= $validation->showError('username', 'my_single') ?>

Creating Custom Rules
************************************************

Rules are stored within simple, namespaced classes. They can be stored any location you would like, as long as the
autoloader can find it. These files are called RuleSets. To add a new RuleSet, edit **Config/Validation.php** and
add the new file to the ``$ruleSets`` array::

    public $ruleSets = [
		\CodeIgniter\Validation\Rules::class,
		\CodeIgniter\Validation\FileRules::class,
		\CodeIgniter\Validation\CreditCardRules::class,
	];

You can add it as either a simple string with the fully qualified class name, or using the ``::class`` suffix as
shown above. The primary benefit here is that it provides some extra navigation capabilities in more advanced IDEs.

Within the file itself, each method is a rule and must accept a string as the first parameter, and must return
a boolean true or false value signifying true if it passed the test or false if it did not::

    class MyRules
    {
        public function even(string $str): bool
        {
            return (int)$str % 2 == 0;
        }
    }

By default, the system will look within ``CodeIgniter\Language\en\Validation.php`` for the language strings used
within errors. In custom rules, you may provide error messages by accepting a $error variable by reference in the
second parameter::

    public function even(string $str, string &$error = null): bool
    {
        if ((int)$str % 2 != 0)
        {
            $error = lang('myerrors.evenError');
            return false;
        }

        return true;
    }

Your new custom rule could now be used just like any other rule::

    $this->validate($request, [
        'foo' => 'required|even'
    ]);

Allowing Parameters
===================

If your method needs to work with parameters, the function will need a minimum of three parameters: the string to validate,
the parameter string, and an array with all of the data that was submitted the form. The $data array is especially handy
for rules like ``require_with`` that needs to check the value of another submitted field to base its result on::

	public function required_with($str, string $fields, array $data): bool
	{
		$fields = explode(',', $fields);

		// If the field is present we can safely assume that
		// the field is here, no matter whether the corresponding
		// search field is present or not.
		$present = $this->required($str ?? '');

		if ($present)
		{
			return true;
		}

        // Still here? Then we fail this test if
		// any of the fields are present in $data
		// as $fields is the lis
		$requiredFields = [];

		foreach ($fields as $field)
		{
			if (array_key_exists($field, $data))
			{
				$requiredFields[] = $field;
			}
		}

		// Remove any keys with empty values since, that means they
		// weren't truly there, as far as this is concerned.
		$requiredFields = array_filter($requiredFields, function ($item) use ($data) {
			return ! empty($data[$item]);
		});

		return empty($requiredFields);
	}

Custom errors can be returned as the fourth parameter, just as described above.

Available Rules
***************

The following is a list of all the native rules that are available to use:

.. note:: Rule is a string; there must be no spaces between the parameters, especially the "is_unique" rule.
	There can be no spaces before and after "ignore_value".

- "is_unique[supplier.name,uuid, $uuid]"   is not ok
- "is_unique[supplier.name,uuid,$uuid ]"   is not ok
- "is_unique[supplier.name,uuid,$uuid]"    is ok

======================= =========== =============================================================================================== ===================================================
Rule                    Parameter   Description                                                                                     Example
======================= =========== =============================================================================================== ===================================================
alpha                   No          Fails if field has anything other than alphabetic characters.
alpha_space             No          Fails if field contains anything other than alphabetic characters or spaces.
alpha_dash              No          Fails if field contains anything other than alpha-numeric characters, underscores or dashes.
alpha_numeric           No          Fails if field contains anything other than alpha-numeric characters or numbers.
alpha_numeric_space     No          Fails if field contains anything other than alpha-numeric characters, numbers or space.
decimal                 No          Fails if field contains anything other than a decimal number.
differs                 Yes         Fails if field does not differ from the one in the parameter.                                   differs[field_name]
exact_length            Yes         Fails if field is not exactly the parameter value. One or more comma-separated values.          exact_length[5] or exact_length[5,8,12]
greater_than            Yes         Fails if field is less than or equal to the parameter value or not numeric.                     greater_than[8]
greater_than_equal_to   Yes         Fails if field is less than the parameter value, or not numeric.                                greater_than_equal_to[5]
if_exist                No          If this rule is present, validation will only return possible errors if the field key exists,
                                    regardless of its value.
in_list                 Yes         Fails if field is not within a predetermined list.                                              in_list[red,blue,green]
integer                 No          Fails if field contains anything other than an integer.
is_natural              No          Fails if field contains anything other than a natural number: 0, 1, 2, 3, etc.
is_natural_no_zero      No          Fails if field contains anything other than a natural number, except zero: 1, 2, 3, etc.
is_unique               Yes         Checks if this field value exists in the database. Optionally set a                             is_unique[table.field,ignore_field,ignore_value]
                                    column and value to ignore, useful when updating records to ignore itself.
less_than               Yes         Fails if field is greater than or equal to the parameter value or not numeric.                  less_than[8]
less_then_equal_to      Yes         Fails if field is greater than the parameter value or not numeric.                              less_than_equal_to[8]
matches                 Yes         The value must match the value of the field in the parameter.                                   matches[field]
max_length              Yes         Fails if field is longer than the parameter value.                                              max_length[8]
min_length              Yes         Fails if field is shorter than the parameter value.                                             min_length[3]
numeric                 No          Fails if field contains anything other than numeric characters.
regex_match             Yes         Fails if field does not match the regular expression.                                           regex_match[/regex/]
permit_empty            No          Allows the field to receive an empty array, empty string, null or false.
required                No          Fails if the field is an empty array, empty string, null or false.
required_with           Yes         The field is required when any of the other required fields are present in the data.            required_with[field1,field2]
required_without        Yes         The field is required when all of the other fields are present in the data but not required.    required_without[field1,field2]
string                  No          A generic alternative to the alpha* rules that confirms the element is a string
timezone                No          Fails if field does match a timezone per ``timezone_identifiers_list``
valid_base64            No          Fails if field contains anything other than valid Base64 characters.
valid_json              No          Fails if field does not contain a valid JSON string.
valid_email             No          Fails if field does not contain a valid email address.
valid_emails            No          Fails if any value provided in a comma separated list is not a valid email.
valid_ip                No          Fails if the supplied IP is not valid. Accepts an optional parameter of ‘ipv4’ or                valid_ip[ipv6]
                                    ‘ipv6’ to specify an IP format.
valid_url               No          Fails if field does not contain a valid URL.
valid_date              No          Fails if field does not contain a valid date. Accepts an optional parameter                      valid_date[d/m/Y]
                                    to matches a date format.
valid_cc_number         Yes         Verifies that the credit card number matches the format used by the specified provider.          valid_cc_number[amex]
                                    Current supported providers are: American Express (amex), China Unionpay (unionpay),
                                    Diners Club CarteBlance (carteblanche), Diners Club (dinersclub), Discover Card (discover),
                                    Interpayment (interpayment), JCB (jcb), Maestro (maestro), Dankort (dankort), NSPK MIR (mir),
                                    Troy (troy), MasterCard (mastercard), Visa (visa), UATP (uatp), Verve (verve),
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
    $this->validate([
        'avatar' => 'uploaded[avatar]|max_size[avatar,1024]'
    ]);

======================= =========== =============================================================================================== ========================================
Rule                    Parameter   Description                                                                                     Example
======================= =========== =============================================================================================== ========================================
uploaded                Yes         Fails if the name of the parameter does not match the name of any uploaded files.               uploaded[field_name]
max_size                Yes         Fails if the uploaded file named in the parameter is larger than the second parameter in        max_size[field_name,2048]
                                    kilobytes (kb).
max_dims                Yes         Fails if the maximum width and height of an uploaded image exceed values. The first parameter   max_dims[field_name,300,150]
                                    is the field name. The second is the width, and the third is the height. Will also fail if
                                    the file cannot be determined to be an image.
mime_in                 Yes         Fails if the file's mime type is not one listed in the parameters.                              mime_in[field_name,image/png,image/jpg]
ext_in                  Yes         Fails if the file's extension is not one listed in the parameters.                              ext_in[field_name,png,jpg,gif]
is_image                Yes         Fails if the file cannot be determined to be an image based on the mime type.                   is_image[field_name]
======================= =========== =============================================================================================== ========================================

.. note:: You can also use any native PHP functions that permit up
	to two parameters, where at least one is required (to pass
	the field data).
