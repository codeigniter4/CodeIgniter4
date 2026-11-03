# Validation

CodeIgniter provides a comprehensive data validation class that helps minimize the amount of code you'll write.

## Overview

Before explaining CodeIgniter's approach to data validation, let's describe the ideal scenario:

1.  A form is displayed.
2.  You fill it in and submit it.
3.  If you submitted something invalid, or perhaps missed a required item, the form is redisplayed containing your data along with an error message describing the problem.
4.  This process continues until you have submitted a valid form.

On the receiving end, the script must:

1.  Check for required data.
2.  Verify that the data is of the correct type, and meets the correct criteria. For example, if a username is submitted it must be validated to contain only permitted characters. It must be of a minimum length, and not exceed a maximum length. The username can't be someone else's existing username, or perhaps even a reserved word. Etc.
3.  Sanitize the data for security.
4.  Pre-format the data if needed.
5.  Prep the data for insertion in the database.

Although there is nothing terribly complex about the above process, it usually requires a significant amount of code, and to display error messages, various control structures are usually placed within the form HTML. Form validation, while simple to create, is generally very messy and tedious to implement.

## Form Validation Tutorial

What follows is a "hands-on" tutorial for implementing CodeIgniter's Form Validation.

In order to implement form validation, you'll need three things:

1.  A [View](../outgoing/views.md) file containing a form.
2.  A View file containing a "success" message to be displayed upon successful submission.
3.  A [controller](../incoming/controllers.md) method to receive and process the submitted data.

Let's create those three things, using a member sign-up form as the example.

### The Form

Using a text editor, create a form called **signup.php**. In it, place this code and save it to your **app/Views/** folder:

```
<html>
<head>
    <title>My Form</title>
</head>
<body>

    <?= validation_list_errors() ?>

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
```

### The Success Page

Using a text editor, create a form called **success.php**. In it, place this code and save it to your **app/Views/** folder:

```
<html>
<head>
    <title>My Form</title>
</head>
<body>

    <h3>Your form was successfully submitted!</h3>

    <p><?= anchor('form', 'Try it again!') ?></p>

</body>
</html>
```

### The Controller

Using a text editor, create a controller called **Form.php**. In it, place this code and save it to your **app/Controllers/** folder:

```php
<?php

namespace App\Controllers;

class Form extends BaseController
{
    protected $helpers = ['form'];

    public function index()
    {
        if (! $this->request->is('post')) {
            return view('signup');
        }

        $rules = [
            // @TODO
        ];

        $data = $this->request->getPost(array_keys($rules));

        if (! $this->validateData($data, $rules)) {
            return view('signup');
        }

        // If you want to get the validated data.
        $validData = $this->validator->getValidated();

        return view('success');
    }
}
```

!!! note "Note"
    The [$this->request->is()](../incoming/incomingrequest.md#is) method can be used since v4.3.0. In previous versions, you need to use `if (strtolower($this->request->getMethod()) !== 'post')`.

!!! note "Note"
    The [$this->validator->getValidated()](#getting-validated-data) method can be used since v4.4.0.

### The Routes

Then add routes for the controller in **app/Config/Routes.php**:

```php
// ...

$routes->get('form', 'Form::index');
$routes->post('form', 'Form::index');

// ...
```

### Try it!

To try your form, visit your site using a URL similar to this one:

```
example.com/index.php/form/
```

If you submit the form, you should simply see the form reload. That's because you haven't set up any validation rules in [$this->validateData()](../incoming/controllers.md#this-validatedata) yet.

The `validateData()` method is a method in the Controller. It uses the **Validation class** inside. See [$this->validateData()](../incoming/controllers.md#this-validatedata).

!!! note "Note"
    Since you haven't told the `validateData()` method to validate anything yet, it **returns false** (boolean false) **by default**. The `validateData()` method only returns true if it has successfully applied your rules without any of them failing.

### Explanation

You'll notice several things about the above pages.

#### signup.php

The form (**signup.php**) is a standard web form with a couple of exceptions:

1.  It uses a [form helper](../helpers/form_helper.md) to create the form opening and closing. Technically, this isn't necessary. You could create the form using standard HTML. However, the benefit of using the helper is that it generates the action URL for you, based on the URL in your config file. This makes your application more portable in the event your URLs change.

2.  At the top of the form, you'll notice the following function call:

    ```
     <?= validation_list_errors() ?>

    This function will return any error messages sent back by the validator. If there are no messages, it returns an
    empty string.
    ```

#### Form.php

The controller (**Form.php**) has one property: `$helpers`. It loads the form helper used by your view files.

The controller has one method: `index()`. This method returns the **signup** view to show the form when a non-POST request comes. Otherwise, it uses the Controller-provided [$this->validateData()](../incoming/controllers.md#this-validatedata) method. It also runs the validation routine. Based on whether the validation was successful, it either presents the form or the success page.

### Add Validation Rules

Then add validation rules in the controller (**Form.php**):

```php
// ...

$rules = [
    'username' => 'required|max_length[30]',
    'password' => 'required|max_length[255]|min_length[10]',
    'passconf' => 'required|max_length[255]|matches[password]',
    'email'    => 'required|max_length[254]|valid_email',
];

// ...
```

If you submit the form, you should see the success page or the form with error messages.

## Config for Validation

### Traditional and Strict Rules

CodeIgniter 4 has two kinds of Validation rule classes.

The default rule classes (**Strict Rules**) have the namespace `CodeIgniter\Validation\StrictRules`, and they provide strict validation.

The traditional rule classes (**Traditional Rules**) have the namespace `CodeIgniter\Validation`. They are provided for backward compatibility only. They may not validate non-string values correctly and need not be used in new projects.

!!! note "Note"
    Since v4.3.0, **Strict Rules** are used by default for better security.

#### Strict Rules

!!! success "Available from version 4.2.0"

The **Strict Rules** don't use implicit type conversion.

#### Traditional Rules

!!! important "Important"
    Traditional Rules exist only for backward compatibility. Do not use them in new projects. Even if you are already using them, we recommend switching to Strict Rules.

!!! warning "Warning"
    When validating data that contains non-string values, such as JSON data, you should use **Strict Rules**.

The **Traditional Rules** implicitly assume that string values are validated, and the input value may be converted implicitly to a string value. It works for most basic cases like validating POST data.

However, for example, if you use JSON input data, it may be of type bool/null/array. When you validate the boolean `true`, it is converted to the string `'1'` with the Traditional rule classes. If you validate it with the `integer` rule, `'1'` passes the validation.

#### Using Traditional Rules

!!! warning "Warning"
    The **Traditional Rules** are provided for backward compatibility only. They may not validate non-string values correctly and need not be used in new projects.

If you want to use traditional rules, you need to change the rule classes in **app/Config/Validation.php**:

```php
<?php

namespace Config;

// ...

class Validation extends BaseConfig
{
    // ...

    public array $ruleSets = [
        \CodeIgniter\Validation\CreditCardRules::class,
        \CodeIgniter\Validation\FileRules::class,
        \CodeIgniter\Validation\FormatRules::class,
        \CodeIgniter\Validation\Rules::class,
    ];

    // ...
}
```

## Loading the Library

The library is loaded as a service named **validation**:

```php
$validation = service('validation');
```

This automatically loads the `Config\Validation` file, which contains settings for including multiple Rulesets, and collections of rules that can be easily reused.

!!! note "Note"
    You may never need to use this method, as both the [Controller](../incoming/controllers.md) and the [Model](../models/model.md) provide methods to make validation even easier.

## How Validation Works

- The validation never changes the data to be validated.
- The validation checks each field in turn according to the Validation Rules you set. If any rule returns false, the check for that field ends there.
- The Format Rules do not permit an empty string. If you want to permit an empty string, add the `permit_empty` rule.
- If a field does not exist in the data to be validated, the value is interpreted as `null`. If you want to check that the field exists, add the `field_exists` rule.

!!! note "Note"
    The `field_exists` rule can be used since v4.5.0.

## Setting Validation Rules

CodeIgniter lets you set as many validation rules as you need for a given field, cascading them in order. To set validation rules, you will use the `setRule()`, `setRules()`, or `withRequest()` methods.

### Setting a Single Rule

#### setRule()

This method sets a single rule. It has the method signature:

```
setRule(string $field, ?string $label, array|string $rules[, array $errors = []])
```

The `$rules` parameter takes in either a pipe-delimited list of rules or an array collection of rules:

```php
$validation->setRule('username', 'Username', 'required|max_length[30]|min_length[3]');
$validation->setRule('password', 'Password', ['required', 'max_length[255]', 'min_length[8]', 'alpha_numeric_punct']);
```

The value you pass to `$field` must match the key of any data array that is sent in. If the data is taken directly from `$_POST`, then it must be an exact match for the form input name.

!!! warning "Warning"
    Prior to v4.2.0, this method's third parameter, `$rules`, was typehinted to accept `string`. In v4.2.0 and after, the typehint was removed to allow arrays, too. To avoid LSP being broken in extending classes overriding this method, the child class's method should also be modified to remove the typehint.

### Setting Multiple Rules

#### setRules()

Like `setRule()`, but accepts an array of field names and their rules:

```php
$validation->setRules([
    'username' => 'required|max_length[30]',
    'password' => 'required|max_length[255]|min_length[10]',
]);
// or
$validation->setRules([
    'username' => ['required', 'max_length[30]'],
    'password' => ['required', 'max_length[255]', 'min_length[10]'],
]);
```

To give a labeled error message, you can set it up as follows:

```php
$validation->setRules([
    'username' => ['label' => 'Username', 'rules' => 'required|max_length[30]'],
    'password' => ['label' => 'Password', 'rules' => 'required|max_length[255]|min_length[10]'],
]);
// or
$validation->setRules([
    'username' => ['label' => 'Username', 'rules' => 'required|max_length[30]'],
    'password' => ['label' => 'Password', 'rules' => ['required', 'max_length[255]', 'min_length[10]']],
]);
```

!!! note "Note"
    `setRules()` will overwrite any rules that were set previously. To add more than one rule to an existing set of rules, use `setRule()` multiple times.

### Setting Rules for Array Data

If your data is in a nested associative array, you can use "dot array syntax" to easily validate your data:

```php
/*
 * The data to test:
 * [
 *     'contacts' => [
 *         'name' => 'Joe Smith',
 *         'friends' => [
 *             [
 *                 'name' => 'Fred Flinstone',
 *             ],
 *             [
 *                 'name' => 'Wilma',
 *             ],
 *         ]
 *     ]
 * ]
 */

// Joe Smith
$validation->setRules([
    'contacts.name' => 'required|max_length[60]',
]);
```

You can use the `*` wildcard symbol to match any one level of the array:

```php
// Fred Flintsone & Wilma
$validation->setRules([
    'contacts.friends.*.name' => 'required|max_length[60]',
]);
```

!!! note "Note"
    Prior to v4.4.4, due to a bug, the wildcard `*` validated data in incorrect dimensions. See [Upgrading](../installation/upgrade_444.md#validation-with-dot-array-syntax) for details.

The "dot array syntax" can also be useful when you have single-dimension array data. For example, data returned by a multi-select dropdown:

```php
/*
 * The data to test:
 * [
 *     'user_ids' => [
 *         1,
 *         2,
 *         3,
 *     ]
 * ]
 */

// Rule
$validation->setRules([
    'user_ids.*' => 'required|max_length[19]',
]);
```

### withRequest()

!!! important "Important"
    This method exists only for backward compatibility. Do not use it in new projects. Even if you are already using it, we recommend that you use another, more appropriate method.

!!! warning "Warning"
    If you want to validate POST data only, don't use `withRequest()`. This method uses [$request->getVar()](../incoming/incomingrequest.md#getting-data) which returns `$_GET`, `$_POST` or `$_COOKIE` data in that order (depending on **php.ini** [request-order](https://www.php.net/manual/en/ini.core.php#ini.request-order)). Newer values override older values. POST values may be overridden by the cookies if they have the same name.

One of the most common times you will use the validation library is when validating data that was input from an HTTP Request. If desired, you can pass an instance of the current Request object and it will take all of the input data and set it as the data to be validated:

```php
$validation = service('validation');
$request    = service('request');

if ($validation->withRequest($request)->run()) {
    // If you use the input data, you should get it from the getValidated() method.
    // Otherwise you may create a vulnerability.
    $validData = $validation->getValidated();

    // ...
}
```

!!! warning "Warning"
    When you use this method, you should use the [getValidated()](#getting-validated-data) method to get the validated data. Because this method gets JSON data from [$request->getJSON()](../incoming/incomingrequest.md#getting-json-data) when the request is a JSON request (`Content-Type: application/json`), or gets raw data from [$request->getRawInput()](../incoming/incomingrequest.md#retrieving-raw-data-put-patch-delete) when the request is a PUT, PATCH, or DELETE request and is not an HTML form post (`Content-Type: multipart/form-data`), or gets data from [$request->getVar()](../incoming/incomingrequest.md#getting-data), and an attacker could change what data is validated.

!!! note "Note"
    The [getValidated()](#getting-validated-data) method can be used since v4.4.0.

## Working with Validation

### Running Validation

The `run()` method runs validation. It has the method signature:

```
run(?array $data = null, ?string $group = null, ?string $dbGroup = null): bool
```

The `$data` is an array of data to validate. The optional second parameter `$group` is the [predefined group of rules](#how-to-save-your-rules) to apply. The optional third parameter `$dbGroup` is the database group to use.

This method returns true if the validation is successful.

```php
if (! $validation->run($data)) {
    // handle validation errors
}
// or
if (! $validation->run($data, 'signup')) {
    // handle validation errors
}
```

### Running Multiple Validations

!!! note "Note"
    The `run()` method will not reset the error state. Should a previous run fail, `run()` will always return false and `getErrors()` will return all previous errors until explicitly reset.

If you intend to run multiple validations, for instance on different data sets or with different rules after one another, you might need to call `$validation->reset()` before each run to get rid of errors from the previous run. Be aware that `reset()` will invalidate any data, rule or custom error you previously set, so `setRules()`, `setRuleGroup()` etc. need to be repeated:

```php
foreach ($userAccounts as $user) {
    $validation->reset();
    $validation->setRules($userAccountRules);

    if (! $validation->run($user)) {
        // handle validation errors
    }
}
```

### Validating 1 Value

The `check()` method validates one value against the rules. The first parameter `$value` is the value to validate. The second parameter `$rule` holds the validation rules. The optional third parameter `$errors` is the custom error message.

```php
if ($validation->check($value, 'required')) {
    // $value is valid.
}
```

!!! note "Note"
    Prior to v4.4.0, this method's second parameter, `$rule`, was typehinted to accept `string`. In v4.4.0 and after, the typehint was removed to allow arrays, too.

!!! note "Note"
    This method calls the `setRule()` method to set the rules internally.

### Getting Validated Data

!!! success "Available from version 4.4.0"

The actual validated data can be retrieved with the `getValidated()` method. This method returns an array of only those elements that have been validated by the validation rules.

```php
$validation = service('validation');
$validation->setRules([
    'username' => 'required',
    'password' => 'required|min_length[10]',
]);

$data = [
    'username'   => 'john',
    'password'   => 'BPi-$Swu7U5lm$dX',
    'csrf_token' => '8b9218a55906f9dcc1dc263dce7f005a',
];

if ($validation->run($data)) {
    $validatedData = $validation->getValidated();
    // $validatedData = [
    //     'username' => 'john',
    //     'password' => 'BPi-$Swu7U5lm$dX',
    // ];
}
```

```php
// In Controller.

if (! $this->validateData($data, [
    'username' => 'required',
    'password' => 'required|min_length[10]',
])) {
    // The validation failed.
    return view('login', [
        'errors' => $this->validator->getErrors(),
    ]);
}

// The validation was successful.

// Get the validated data.
$validData = $this->validator->getValidated();
```

### Saving Sets of Validation Rules to the Config File

A nice feature of the Validation class is that it permits you to store all your validation rules for your entire application in a config file. You organize the rules into "groups". You can specify a different group every time you run the validation.

#### How to Save Your Rules

To store your validation rules, simply create a new public property in the `Config\Validation` class with the name of your group. This element will hold an array with your validation rules. As shown earlier, the validation array will have this prototype:

```php
<?php

namespace Config;

// ...

class Validation extends BaseConfig
{
    // ...

    public array $signup = [
        'username'     => 'required|max_length[30]',
        'password'     => 'required|max_length[255]',
        'pass_confirm' => 'required|max_length[255]|matches[password]',
        'email'        => 'required|max_length[254]|valid_email',
    ];

    // ...
}
```

#### How to Specify Rule Group

You can specify the group to use when you call the `run()` method:

```php
$validation->run($data, 'signup');
```

#### How to Save Error Messages

You can also store custom error messages in this configuration file by naming the property the same as the group, with `_errors` appended. These will automatically be used for any errors when this group is used:

```php
<?php

namespace Config;

// ...

class Validation extends BaseConfig
{
    // ...

    public array $signup = [
        'username'     => 'required|max_length[30]',
        'password'     => 'required|max_length[255]',
        'pass_confirm' => 'required|max_length[255]|matches[password]',
        'email'        => 'required|max_length[254]|valid_email',
    ];

    public array $signup_errors = [
        'username' => [
            'required' => 'You must choose a username.',
        ],
        'email' => [
            'valid_email' => 'Please check the Email field. It does not appear to be valid.',
        ],
    ];

    // ...
}
```

Or pass all settings in an array:

```php
<?php

namespace Config;

// ...

class Validation extends BaseConfig
{
    // ...

    public array $signup = [
        'username' => [
            'rules'  => 'required|max_length[30]',
            'errors' => [
                'required' => 'You must choose a Username.',
            ],
        ],
        'email' => [
            'rules'  => 'required|max_length[254]|valid_email',
            'errors' => [
                'valid_email' => 'Please check the Email field. It does not appear to be valid.',
            ],
        ],
    ];

    // ...
}
```

See [Setting Custom Error Messages](#setting-custom-error-messages) for details on the formatting of the array.

#### Getting & Setting Rule Groups

##### Get Rule Group

This method gets a rule group from the validation configuration:

```php
$validation->getRuleGroup('signup');
```

##### Set Rule Group

This method sets a rule group from the validation configuration to the validation service:

```php
$validation->setRuleGroup('signup');
```

### Validation Placeholders

The Validation class provides a simple method to replace parts of your rules based on data that's being passed into it. This sounds fairly obscure but can be especially handy with the `is_unique` validation rule.

Placeholders are simply the name of the field (or array key) that was passed in as `$data` surrounded by curly brackets. This will be replaced by the **value** of the matched incoming field. An example should clarify this:

```php
$validation->setRules([
    'id'    => 'max_length[19]|is_natural_no_zero',
    'email' => 'required|max_length[254]|valid_email|is_unique[users.email,id,{id}]',
]);
```

!!! warning "Warning"
    Since v4.3.5, you must set the validation rules for the placeholder field (the `id` field in the sample code above) for security reasons, because attackers can send any data to your application.

This set of rules states that the email address should be unique in the database, except for the row that has an id matching the placeholder's value. Assuming that the form POST data had the following:

```php
$_POST = [
    'id'    => 4,
    'email' => 'foo@example.com',
];
```

then the `{id}` placeholder would be replaced with the number **4**, giving this revised rule:

```php
$validation->setRules([
    'id'    => 'max_length[19]|is_natural_no_zero',
    'email' => 'required|max_length[254]|valid_email|is_unique[users.email,id,4]',
]);
```

So it will ignore the row in the database that has `id=4` when it verifies the email is unique.

!!! note "Note"
    Since v4.3.5, if the placeholder (`id`) value does not pass the validation, the placeholder would not be replaced.

This can also be used to create more dynamic rules at runtime, as long as you take care that any dynamic keys passed in don't conflict with your form data.

## Working with Errors

The Validation library provides several methods to help you set error messages, provide custom error messages, and retrieve one or more errors to display.

By default, error messages are derived from language strings in **system/Language/en/Validation.php**, where each rule has an entry. In case you want to change a message default, create a file **app/Language/en/Validation.php** (and/or the corresponding folder of the locale you use in place of/besides `en`) and place in it keys and values of those error messages for which you want different defaults.

### Setting Custom Error Messages

Both the `setRule()` and `setRules()` methods can accept an array of custom messages that will be used as errors specific to each field as their last parameter. This allows for a very pleasant experience for the user since the errors are tailored to each instance. If no custom error message is provided, the default value will be used.

There are two ways to provide custom error messages.

As the last parameter:

```php
$validation->setRules(
    [
        'username' => 'required|max_length[30]|is_unique[users.username]',
        'password' => 'required|max_length[254]|min_length[10]',
    ],
    [   // Errors
        'username' => [
            'required' => 'All accounts must have usernames provided',
        ],
        'password' => [
            'min_length' => 'Your password is too short. You want to get hacked?',
        ],
    ],
);
```

Or in a labeled style:

```php
$validation->setRules([
    'username' => [
        'label'  => 'Username',
        'rules'  => 'required|max_length[30]|is_unique[users.username]',
        'errors' => [
            'required' => 'All accounts must have {field} provided',
        ],
    ],
    'password' => [
        'label'  => 'Password',
        'rules'  => 'required|max_length[255]|min_length[10]',
        'errors' => [
            'min_length' => 'Your {field} is too short. You want to get hacked?',
        ],
    ],
]);
```

If you'd like to include a field's "human" name, or the optional parameter some rules allow for (such as max_length), or the value that was validated, you can add the `{field}`, `{param}` and `{value}` tags to your message, respectively:

```
'min_length' => 'Supplied value ({value}) for {field} must have at least {param} characters.'
```

On a field with the human name Username and a rule of `min_length[6]` with a value of "Pizza", an error would display: "Supplied value (Pizza) for Username must have at least 6 characters."

!!! warning "Warning"
    If you get the error messages with `getErrors()` or `getError()`, the messages are not HTML-escaped. If you use user input data like `({value})` to make the error message, it might contain HTML tags. If you don't escape the messages before displaying them, XSS attacks are possible.

!!! note "Note"
    When using label-style error messages, if you pass the second parameter to `setRules()`, it will be overwritten with the value of the first parameter.

### Translation of Messages and Validation Labels

To use translated strings from language files, we can simply use the dot syntax. Let's say we have a file with translations located here: **app/Language/en/Rules.php**. We can simply use the language lines defined in this file, like this:

```php
$validation->setRules([
    'username' => [
        'label'  => 'Rules.username',
        'rules'  => 'required|max_length[30]|is_unique[users.username]',
        'errors' => [
            'required' => 'Rules.username.required',
        ],
    ],
    'password' => [
        'label'  => 'Rules.password',
        'rules'  => 'required|max_length[255]|min_length[10]',
        'errors' => [
            'min_length' => 'Rules.password.min_length',
        ],
    ],
]);
```

### Getting All Errors

If you need to retrieve all error messages for failed fields, you can use the `getErrors()` method:

```php
$errors = $validation->getErrors();
/*
 * Produces:
 * [
 *     'field1' => 'error message',
 *     'field2' => 'error message',
 * ]
 */
```

If no errors exist, an empty array will be returned.

When using a wildcard (`*`), the error will point to a specific field, replacing the asterisk with the appropriate key/keys:

```
// for data
'contacts' => [
    'friends' => [
        [
            'name' => 'Fred Flintstone',
        ],
        [
            'name' => '',
        ],
    ]
]

// rule
'contacts.friends.*.name' => 'required'

// error will be
'contacts.friends.1.name' => 'The contacts.friends.*.name field is required.'
```

### Getting a Single Error

You can retrieve the error for a single field with the `getError()` method. The only parameter is the field name:

```php
$error = $validation->getError('username');
```

If no error exists, an empty string will be returned.

!!! note "Note"
    When using a wildcard, all found errors that match the mask will be combined into one line separated by the EOL character.

### Check If Error Exists

You can check to see if an error exists with the `hasError()` method. The only parameter is the field name:

```php
if ($validation->hasError('username')) {
    echo $validation->getError('username');
}
```

When specifying a field with a wildcard, all errors matching the mask will be checked:

```php
/*
 * For errors:
 * [
 *     'foo.0.bar'   => 'Error',
 *     'foo.baz.bar' => 'Error',
 * ]
 */

// returns true
$validation->hasError('foo.*.bar');
```

### Redirect and Validation Errors

PHP shares nothing between requests. So when you redirect if a validation fails, there will be no validation errors in the redirected request because the validation has run in the previous request.

In that case, you need to use the Form helper functions `validation_errors()`, `validation_list_errors()` and `validation_show_error()`. These functions check the validation errors that are stored in the session.

To store the validation errors in the session, you need to use `withInput()` with `redirect()`:

```php
// In Controller.
if (! $this->validateData($data, $rules)) {
    return redirect()->back()->withInput();
}
```

## Customizing Error Display

When you call `$validation->listErrors()` or `$validation->showError()`, it loads a view file in the background that determines how the errors are displayed. By default, they display with a class of `errors` on the wrapping div. You can easily create new views and use them throughout your application.

### Creating the Views

The first step is to create custom views. These can be placed anywhere that the `view()` method can locate them, which means the standard View directory or any namespaced View folder will work. For example, you could create a new view at **app/Views/\_errors_list.php**:

```php
<?php if (! empty($errors)): ?>
    <div class="alert alert-danger" role="alert">
        <ul>
        <?php foreach ($errors as $error): ?>
            <li><?= esc($error) ?></li>
        <?php endforeach ?>
        </ul>
    </div>
<?php endif ?>
```

An array named `$errors` is available within the view that contains a list of the errors, where the key is the name of the field that had the error, and the value is the error message, like this:

```php
$errors = [
    'username' => 'The username field must be unique.',
    'email'    => 'You must provide a valid email address.',
];
```

There are actually two types of views that you can create. The first has an array of all of the errors, and is what we just looked at. The other type is simpler, and only contains a single variable, `$error`, that contains the error message. This is used with the `showError()` method where a field must be specified:

```
<span class="help-block"><?= esc($error) ?></span>
```

### Configuration

Once you have your views created, you need to let the Validation library know about them. Open **app/Config/Validation.php**. Inside, you'll find the `$templates` property where you can list as many custom views as you want, and provide a short alias they can be referenced by. If we were to add our example file from above, it would look something like:

```php
<?php

namespace Config;

// ...

class Validation extends BaseConfig
{
    // ...

    public array $templates = [
        'list'    => 'CodeIgniter\Validation\Views\list',
        'single'  => 'CodeIgniter\Validation\Views\single',
        'my_list' => '_errors_list',
    ];

    // ...
}
```

### Specifying the Template

You can specify the template to use by passing its alias as the first parameter in `listErrors()`:

```
<?= $validation->listErrors('my_list') ?>
```

When showing field-specific errors, you can pass the alias as the second parameter to the `showError()` method, right after the name of the field the error should belong to:

```
<?= $validation->showError('username', 'my_single') ?>
```

## Creating Custom Rules

### Using Rule Classes

Rules are stored within simple, namespaced classes. They can be stored in any location you would like, as long as the autoloader can find them. These files are called RuleSets.

#### Adding a RuleSet

To add a new RuleSet, edit **app/Config/Validation.php** and add the new file to the `$ruleSets` array:

```php
<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Validation\CreditCardRules;
use CodeIgniter\Validation\FileRules;
use CodeIgniter\Validation\FormatRules;
use CodeIgniter\Validation\Rules;

class Validation extends BaseConfig
{
    // ...

    public array $ruleSets = [
        Rules::class,
        FormatRules::class,
        FileRules::class,
        CreditCardRules::class,
    ];

    // ...
}
```

You can add it as either a simple string with the fully qualified class name, or using the `::class` suffix as shown above. The primary benefit of using the `::class` suffix is that it provides some extra navigation capabilities in more advanced IDEs.

#### Creating a Rule Class

Within the file itself, each method is a rule and must accept a value to validate as the first parameter, and must return a boolean true or false value signifying true if it passed the test or false if it did not:

```php
<?php

class MyRules
{
    public function even($value): bool
    {
        return (int) $value % 2 === 0;
    }
}
```

By default, the system will look within **system/Language/en/Validation.php** for the language strings used within errors. To provide default error messages for your custom rules, you may place them in **app/Language/en/Validation.php** (and/or the corresponding folder of the locale you use in place of `en`). Also, in case you want to use some other language string file in place of the default **Validation.php**, you may provide error messages by accepting an `&$error` variable by reference in the second (or, in case your rule needs to work with parameters, as described below – the fourth) parameter:

```php
<?php

class MyRules
{
    public function even($value, ?string &$error = null): bool
    {
        if ((int) $value % 2 !== 0) {
            $error = lang('myerrors.evenError');

            return false;
        }

        return true;
    }
}
```

#### Using a Custom Rule

Your new custom rule could now be used just like any other rule:

```php
$validation->setRules([
    'foo' => 'required|max_length[19]|even',
]);
```

#### Allowing Parameters

If your method needs to work with parameters, the function will need a minimum of three parameters:

1.  the value to validate (`$value`)
2.  the parameter string (`$params`)
3.  an array with all of the data that was submitted with the form (`$data`)
4.  (optional) a custom error string (`&$error`), just as described above.

!!! warning "Warning"
    The field values in `$data` are unvalidated (or may be invalid). Using unvalidated input data is a source of vulnerability. You must perform the necessary validation within your custom rules before using the data in `$data`.

The `$data` array is especially handy for rules like `required_with` that need to check the value of another submitted field to base its result on:

```php
<?php

class MyRules
{
    public function required_with($value, string $params, array $data): bool
    {
        $params = explode(',', $params);

        // If the field is present we can safely assume that
        // the field is here, no matter whether the corresponding
        // search field is present or not.
        $present = $this->required($value ?? '');

        if ($present) {
            return true;
        }

        // Still here? Then we fail this test if
        // any of the fields are present in $data
        // as $fields in the list
        $requiredFields = [];

        foreach ($params as $field) {
            if (array_key_exists($field, $data)) {
                $requiredFields[] = $field;
            }
        }

        // Remove any keys with empty values since, that means they
        // weren't truly there, as far as this is concerned.
        $requiredFields = array_filter($requiredFields, static fn ($item) => ! empty($data[$item]));

        return empty($requiredFields);
    }
}
```

### Using Closure Rule

!!! success "Available from version 4.3.0"

If you only need the functionality of a custom rule once throughout your application, you may use a closure instead of a rule class.

You need to use an array for validation rules:

```php
$validation->setRules(
    [
        'foo' => [
            'required',
            static fn ($value) => (int) $value % 2 === 0,
        ],
    ],
    [
        // Errors
        'foo' => [
            // Specify the array key for the closure rule.
            1 => 'The value is not even.',
        ],
    ],
);

if (! $validation->run($data)) {
    // handle validation errors
}
```

You must set the error message for the closure rule. When you specify the error message, set the array key for the closure rule. In the above code, the `required` rule has the key `0`, and the closure has `1`.

Or you can use the following parameters:

```php
$validation->setRules([
    'foo' => [
        'required',
        static function ($value, $data, &$error, $field) {
            if ((int) $value % 2 === 0) {
                return true;
            }

            $error = 'The value is not even.';

            return false;
        },
    ],
]);
```

### Using Callable Rule

!!! success "Available from version 4.5.0"

If you would like to use an array callback as a rule, you may use it instead of a Closure Rule.

You need to use an array for validation rules:

```php
namespace App\Controllers;

class Form extends BaseController
{
    // Define a custom validation rule.
    public function _ruleEven($value): bool
    {
        return (int) $value % 2 === 0;
    }

    public function process()
    {
        // ...

        $validation = service('validation');
        $validation->setRules(
            [
                'foo' => [
                    'required',
                    // Specify the method in this controller as a rule.
                    [$this, '_ruleEven'],
                ],
            ],
            [
                // Errors
                'foo' => [
                    // Specify the array key for the callable rule.
                    1 => 'The value is not even.',
                ],
            ],
        );

        if (! $validation->run($data)) {
            // handle validation errors
        }

        // ...
    }
}
```

You must set the error message for the callable rule. When you specify the error message, set the array key for the callable rule. In the above code, the `required` rule has the key `0`, and the callable has `1`.

Or you can use the following parameters:

```php
namespace App\Controllers;

use Config\Services;

class Form extends BaseController
{
    // Define a custom validation rule.
    public function _ruleEven($value, $data, &$error, $field): bool
    {
        if ((int) $value % 2 === 0) {
            return true;
        }

        $error = 'The value is not even.';

        return false;
    }

    // ...
}
```

## Available Rules

!!! note "Note"
    A rule is a string, so there must be **no spaces** between the parameters, especially for the `is_unique` rule. There can be no spaces before and after `ignore_value`.

```php
// is_unique[table.field,ignore_field,ignore_value]

$validation->setRules([
    'name' => "max_length[36]|is_unique[supplier.name,uuid, {$uuid}]", // is not ok
    'name' => "max_length[36]|is_unique[supplier.name,uuid,{$uuid} ]", // is not ok
    'name' => "max_length[36]|is_unique[supplier.name,uuid,{$uuid}]",  // is ok
    'name' => 'max_length[36]|is_unique[supplier.name,uuid,{uuid}]',   // is ok - see "Validation Placeholders"
]);
// Warning: If `$uuid` is a user input, be sure to validate the format of the value before using it.
// Otherwise, it is vulnerable.
```

### Rules for General Use

The following is a list of all the native rules that are available to use:

| Rule | Parameter | Description | Example |
|----|----|----|----|
| alpha | No | Fails if field has anything other than alphabetic characters in ASCII or is empty. |  |
| alpha_dash | No | Fails if field contains anything other than alphanumeric characters, underscores or dashes in ASCII or is empty. |  |
| alpha_numeric | No | Fails if field contains anything other than alphanumeric characters in ASCII or is empty. |  |
| alpha_numeric_punct | No | Fails if field contains anything other than alphanumeric, space, or this limited set of punctuation characters: `~` (tilde), `!` (exclamation), `#` (number), `$` (dollar), `%` (percent), `&` (ampersand), `*` (asterisk), `-` (dash), `_` (underscore), `+` (plus), `=` (equals), `|` (vertical bar), `:` (colon), `.` (period) or is empty. |  |
| alpha_numeric_space | No | Fails if field contains anything other than alphanumeric or space characters in ASCII or is empty. |  |
| alpha_space | No | Fails if field contains anything other than alphabetic characters or spaces in ASCII or is empty. |  |
| decimal | No | Fails if field contains anything other than a decimal number. Also accepts a `+` or `-` sign for the number. |  |
| differs | Yes | Fails if field does not differ from the one in the parameter. | `differs[field_name]` |
| exact_length | Yes | Fails if field length is not exactly the parameter value. One or more comma-separated values are possible. | `exact_length[5]` or `exact_length[5,8,12]` |
| field_exists | Yes | Fails if field does not exist. (This rule was added in v4.5.0.) |  |
| greater_than | Yes | Fails if field is less than or equal to the parameter value or not numeric. | `greater_than[8]` |
| greater_than_equal_to | Yes | Fails if field is less than the parameter value, or not numeric. | `greater_than_equal_to[5]` |
| hex | No | Fails if field contains anything other than hexadecimal characters. |  |
| if_exist | No | If this rule is present, validation will check the field only when the field key exists in the data to validate. |  |
| in_list | Yes | Fails if field is not within a predetermined list. | `in_list[red,blue,green]` |
| integer | No | Fails if field contains anything other than an integer. |  |
| is_natural | No | Fails if field contains anything other than a natural number: `0`, `1`, `2`, `3`, etc. |  |
| is_natural_no_zero | No | Fails if field contains anything other than a natural number, except zero: `1`, `2`, `3`, etc. |  |
| is_not_unique | Yes | Checks the database to see if the given value exists. Can ignore records by field/value to filter (currently accepts only one filter). (Since v4.6.0, you can optionally pass the dbGroup as a parameter) | `is_not_unique[table.field,where_field,where_value]` or `is_not_unique[dbGroup.table.field,where_field,where_value]` |
| is_unique | Yes | Checks if this field value exists in the database. Optionally set a column and value to ignore, useful when updating records to ignore itself. (Since v4.6.0, you can optionally pass the dbGroup as a parameter) | `is_unique[table.field,ignore_field,ignore_value]` or `is_unique[dbGroup.table.field,ignore_field,ignore_value]` |
| less_than | Yes | Fails if field is greater than or equal to the parameter value or not numeric. | `less_than[8]` |
| less_than_equal_to | Yes | Fails if field is greater than the parameter value or not numeric. | `less_than_equal_to[8]` |
| matches | Yes | The value must match the value of the field in the parameter. | `matches[field]` |
| max_length | Yes | Fails if field is longer than the parameter value. | `max_length[8]` |
| min_length | Yes | Fails if field is shorter than the parameter value. | `min_length[3]` |
| not_in_list | Yes | Fails if field is within a predetermined list. | `not_in_list[red,blue,green]` |
| numeric | No | Fails if field contains anything other than numeric characters. |  |
| permit_empty | No | Allows the field to receive an empty array, empty string, null or false. On success, it skips all other validation rules except for required_with and required_without. |  |
| regex_match | Yes | Fails if field does not match the regular expression. **Note:** Since v4.7.0, if you're using a placeholder with this rule, you must use double braces `{{...}}` instead of single ones `{...}`. | `regex_match[/regex/]` |
| required | No | Fails if the field is an empty array, empty string, null or false. |  |
| required_with | Yes | The field is required when any of the other fields is not [empty()](https://www.php.net/manual/en/function.empty.php) in the data. | `required_with[field1,field2]` |
| required_without | Yes | The field is required when any of the other fields is [empty()](https://www.php.net/manual/en/function.empty.php) in the data. | `required_without[field1,field2]` |
| string | No | A generic alternative to the **alpha**\* rules that confirms the element is a string |  |
| timezone | No | Fails if field does not match a timezone per [timezone_identifiers_list()](https://www.php.net/manual/en/function.timezone-identifiers-list.php) |  |
| valid_base64 | No | Fails if field contains anything other than valid Base64 characters. |  |
| valid_cc_number | Yes | Verifies that the credit card number matches the format used by the specified provider. Currently supported providers are: American Express (`amex`), China Unionpay (`unionpay`), Diners Club Carte Blanche (`carteblanche`), Diners Club (`dinersclub`), Discover Card (`discover`), Interpayment (`interpayment`), JCB (`jcb`), Maestro (`maestro`), Dankort (`dankort`), NSPK MIR (`mir`), Troy (`troy`), MasterCard (`mastercard`), Visa (`visa`), UATP (`uatp`), Verve (`verve`), CIBC Convenience Card (`cibc`), Royal Bank of Canada Client Card (`rbc`), TD Canada Trust Access Card (`tdtrust`), Scotiabank Scotia Card (`scotia`), BMO ABM Card (`bmoabm`), HSBC Canada Card (`hsbc`) | `valid_cc_number[amex]` |
| valid_date | Yes | Fails if field does not contain a valid date. Any string that [strtotime()](https://www.php.net/manual/en/function.strtotime.php) accepts is valid if you don't specify an optional parameter that matches a date format. **So it is usually necessary to specify the parameter.** | `valid_date[d/m/Y]` |
| valid_email | No | Fails if field does not contain a valid email address. |  |
| valid_emails | No | Fails if any value provided in a comma-separated list is not a valid email. |  |
| valid_ip | Yes | Fails if the supplied IP is not valid. Accepts an optional parameter of `ipv4` or `ipv6` to specify an IP format. | `valid_ip[ipv6]` |
| valid_json | No | Fails if field does not contain a valid JSON string. |  |
| valid_url | No | Fails if field does not contain (loosely) a URL. Includes simple strings that could be hostnames, like "codeigniter". **Normally,** `valid_url_strict` **should be used.** |  |
| valid_url_strict | Yes | Fails if field does not contain a valid URL. You can optionally specify a list of valid schemes. If not specified, `http,https` are valid. This rule uses PHP's `FILTER_VALIDATE_URL`. | `valid_url_strict[https]` |

!!! note "Note"
    You can also use any native PHP functions that return boolean and permit at least one parameter, the field data to validate.

!!! important "Important"
    The Validation library **never alters the data** to validate.

### Rules for File Uploads

When you validate uploaded files, you must use the rules specifically created for file validation.

!!! important "Important"
    Only rules that are listed in the table below can be used to validate files. Therefore, if you add any general rules, like `permit_empty`, to a file validation rules array or string, the file validation will not work correctly.

Since the value of a file upload HTML field doesn't exist, and is stored in the `$_FILES` global, the name of the input field will need to be used twice: once to specify the field name as you would for any other rule, and again as the first parameter of all file-upload-related rules:

```
// In the HTML
<input type="file" name="avatar">

// In the controller
$this->validateData([], [
    'avatar' => 'uploaded[avatar]|max_size[avatar,1024]',
]);
```

See also [File Upload Form Tutorial](uploaded_files.md#file-upload-form-tutorial).

| Rule | Parameter | Description | Example |
|----|----|----|----|
| uploaded | Yes | Fails if the name of the parameter does not match the name of any uploaded files. If you want the file upload to be optional (not required), do not define this rule. | `uploaded[field_name]` |
| max_size | Yes | Fails if the uploaded file is larger than the second parameter in kilobytes (kb), or if the file is larger than the allowed maximum size declared in the php.ini config file (`upload_max_filesize` directive). | `max_size[field_name,2048]` |
| max_dims | Yes | Fails if the maximum width and height of an uploaded image exceed the values. The first parameter is the field name. The second is the width, and the third is the height. Will also fail if the file cannot be determined to be an image or the upload failed with an error other than `UPLOAD_ERR_NO_FILE`. | `max_dims[field_name,300,150]` |
| min_dims | Yes | Fails if the minimum width and height of an uploaded image do not meet values. The first parameter is the field name. The second is the width, and the third is the height. Will also fail if the file cannot be determined to be an image or the upload failed with an error other than `UPLOAD_ERR_NO_FILE`. (This rule was added in v4.6.0.) | `min_dims[field_name,300,150]` |
| mime_in | Yes | Fails if the file's mime type is not one listed in the parameters. | `mime_in[field_name,image/png,image/jpeg]` |
| ext_in | Yes | Fails if the client filename's extension is not one listed in the parameters, or if the detected MIME type does not match the MIME types associated with the extension. | `ext_in[field_name,png,jpg,gif]` |
| is_image | Yes | Fails if the file cannot be determined to be an image based on the mime type. | `is_image[field_name]` |

The file validation rules apply to both single and multiple file uploads.

The `is_image`, `mime_in`, and `ext_in` rules check the uploaded file and its client filename, but they cannot guarantee that a web server will not execute a file saved under that name. See [Moving Files](uploaded_files.md#moving-files) for safe storage practices.
