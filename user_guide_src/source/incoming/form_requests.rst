.. _form-requests:

#############
Form Requests
#############

.. versionadded:: 4.8.0

A **FormRequest** is a dedicated class that encapsulates the validation rules,
custom error messages, and authorization logic for a single HTTP request.
Instead of writing validation code inside every controller method, you define
it once in a FormRequest class and type-hint it in the controller method
signature - the framework resolves, authorizes, and validates the request
automatically before your method body runs.

.. contents::
    :local:
    :depth: 2

***********************
Creating a Form Request
***********************

Use the ``make:request`` Spark command to generate a new FormRequest class::

    php spark make:request StorePostRequest

This creates **app/Requests/StorePostRequest.php**. Fill in the ``rules()``
method with the same field/rule pairs you would normally pass to
:ref:`validation-running`:

.. literalinclude:: form_requests/001.php
   :lines: 2-

************************************
Using a Form Request in a Controller
************************************

Type-hint the FormRequest class as a parameter of your controller method. The
framework instantiates it, runs authorization and validation, and passes the
resolved object to your method. If validation fails, the default behavior
redirects back with errors for web requests or returns a 422 JSON response for
JSON/AJAX requests - the method body is never reached.

.. literalinclude:: form_requests/002.php
   :lines: 2-

************************
Accessing Validated Data
************************

``validated()`` returns an array containing only the fields that were declared
in ``rules()``. Fields submitted by the client that are not covered by a rule
are silently discarded, protecting against mass-assignment.

.. literalinclude:: form_requests/009.php
   :lines: 2-

Use ``getValidated()`` to read a single validated field and ``hasValidated()``
to check whether a validated key exists, including keys whose value is
``null``. Both methods support dot-array syntax for nested validated data:

.. literalinclude:: form_requests/014.php
   :lines: 2-

Accessing Other Request Data
============================

For anything not covered by ``validated()`` - uploaded files, request headers,
the client IP address, raw input, and so on - use ``$this->request`` as usual.
It is the same :doc:`IncomingRequest </incoming/incomingrequest>` instance that
the FormRequest uses internally:

.. literalinclude:: form_requests/010.php
   :lines: 2-

*******************************************
Route Parameters Alongside a Form Request
*******************************************

Required scalar route parameters come first, then the FormRequest, then any optional
scalar parameters. The framework matches URI segments to scalar parameters positionally
and injects the FormRequest wherever the type hint appears. Parameters declared with
PHP's ``...`` syntax captures all remaining URI segments. Optional scalar parameters
after the FormRequest get their default values when no URI segment is left to fill them.

.. literalinclude:: form_requests/003.php
   :lines: 2-

**************
Closure Routes
**************

FormRequest injection works identically in closure routes and follows the same
signature shape: route parameters first, then the FormRequest, then optional
scalar parameters. The framework resolves it the same way it does for controller
methods.

.. literalinclude:: form_requests/011.php
   :lines: 2-

********************
``_remap()`` Methods
********************

Automatic FormRequest injection is **not** supported for controller methods that
use ``_remap()``. Because ``_remap()`` has a fixed signature
``($method, ...$params)``, there is no typed position for the framework to
inject a FormRequest into.

Instantiate the FormRequest manually inside ``_remap()`` and call
``resolveRequest()`` yourself. The method returns ``null`` on success or a
``ResponseInterface`` when authorization or validation fails:

.. literalinclude:: form_requests/012.php
   :lines: 2-

*********************
Custom Error Messages
*********************

Override ``messages()`` to return field-specific error messages. The format is
identical to the ``$errors`` argument of :ref:`saving-validation-rules-to-config-file`:

.. literalinclude:: form_requests/004.php
   :lines: 2-

*************
Authorization
*************

Override ``isAuthorized()`` to control whether the current user is allowed to make
this request. Return ``false`` to reject the request with a 403 Forbidden
response before validation even runs.

.. literalinclude:: form_requests/005.php
   :lines: 2-

.. note:: The ``isAuthorized()`` check runs before ``prepareForValidation()`` and
    before validation itself. An unauthorized request never reaches the
    validation stage.

*********************************
Preparing Data Before Validation
*********************************

Override ``prepareForValidation(array $data): array`` to normalize or derive
input values before the validation rules are applied. The method receives the
same data array that will be passed to the validator and must return the
(possibly modified) array. This is useful for computed fields such as slugs,
normalized phone numbers, or trimmed strings.

.. literalinclude:: form_requests/006.php
   :lines: 2-

.. note:: ``old()`` returns the original submitted input, not the normalized
    values. Use ``validated()`` to access the processed data after a successful
    request. If you need ``old()`` to reflect normalized values, see
    :ref:`form-request-flash-normalized`.

.. _form-request-validation-data:

****************************
Customizing the Data Source
****************************

By default ``validationData()`` selects the appropriate data source
automatically based on the HTTP method and ``Content-Type`` header:

* **JSON request** (``Content-Type: application/json``) -> decoded JSON body
* **PUT / PATCH / DELETE** (non-multipart) -> raw body via ``getRawInput()``
* **GET / HEAD** -> query-string parameters via ``getGet()``
* **Everything else** (POST, multipart) -> POST body via ``getPost()``

This avoids the pitfalls of :ref:`validation-withrequest`, which mixes GET and
POST data via ``getVar()``.

Override ``validationData()`` when you need a different data source - for
example, to merge GET and POST parameters:

.. literalinclude:: form_requests/007.php
   :lines: 2-

****************************
Customizing Failure Behavior
****************************

Override ``failedValidation()`` and ``failedAuthorization()`` to take full
control of what happens when a request is rejected. Both methods return a
``ResponseInterface`` that the framework sends to the client:

.. literalinclude:: form_requests/008.php
   :lines: 2-

The default ``failedValidation()`` already detects JSON and AJAX requests and
returns the appropriate 422 response automatically. Override it only when you
need a different behavior, such as always responding with JSON even for ordinary
browser requests.

.. _form-request-flash-normalized:

Flashing Normalized Input
=========================

If your ``prepareForValidation()`` transforms visible form fields (for example,
trimming strings or canonicalizing values), ``old()`` will return the original
submitted input because the redirect flashes the raw superglobals. To make
``old()`` reflect the normalized values instead, override ``failedValidation()``
and flash the normalized payload manually:

.. literalinclude:: form_requests/013.php
   :lines: 2-

*****************************************
How the Framework Resolves Form Requests
*****************************************

When the router dispatches a controller method or closure route, the framework
inspects the callable's parameter list using reflection. For each parameter
whose type extends ``FormRequest``:

#. A new instance is created with the current request injected via the
   constructor.
#. ``isAuthorized()`` is called. If it returns ``false``, ``failedAuthorization()``
   is called, and its response is returned to the client.
#. ``validationData()`` collects the data to validate.
#. ``prepareForValidation()`` receives that data and may modify it before the
   rules are applied.
#. ``run()`` executes the validation rules. If it fails, ``failedValidation()``
   is called, and its response is returned to the client.
#. The validated data is stored internally and available via ``validated()``,
   ``getValidated()``, and ``hasValidated()``.
#. The resolved FormRequest object is injected into the controller method or
   closure.

The callable is never invoked if authorization or validation fails. Non-FormRequest
parameters consume URI route segments in declaration order; variadic parameters
receive all remaining segments.

.. note:: Automatic injection does not apply to ``_remap()`` methods. See
    `_remap() Methods`_ above for the manual workaround.
