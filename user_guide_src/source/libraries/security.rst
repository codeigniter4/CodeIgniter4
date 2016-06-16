##############
Security Class
##############

The Security Class contains methods that help protect your site against Cross-Site Request Forgery attacks.

.. contents::
:local:

*******************
Loading the Library
*******************

You do not need to load this library if CSRF protection is turned on, as it is loaded during the bootstrap process.
In other cases, though, you may load it through the Services file::

	$security = \Config\Services::security();

*********************************
Cross-site request forgery (CSRF)
*********************************

You can enable CSRF protection by altering your **application/Config/App.php**
file in the following way::

	public $CSRFProtection  = true;

If you use the :doc:`form helper <../helpers/form_helper>`, then
:func:`form_open()` will automatically insert a hidden csrf field in
your forms. If not, then you can use the always available ``csrf_token()``
and ``csrf_hash()`` functions
::

	<input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />

Tokens may be either regenerated on every submission (default) or
kept the same throughout the life of the CSRF cookie. The default
regeneration of tokens provides stricter security, but may result
in usability concerns as other tokens become invalid (back/forward
navigation, multiple tabs/windows, asynchronous actions, etc). You
may alter this behavior by editing the following config parameter
::

	public $CSRFRegenerate  = true;

Select URIs can be whitelisted from CSRF protection (for example API
endpoints expecting externally POSTed content). You can add these URIs
by editing the 'CSRFExcludeURIs' config parameter::

	public $CSRFExcludeURIs = ['api/person/add'];

Regular expressions are also supported (case-insensitive)::

	public $CSRFExcludeURIs = [
		'api/record/[0-9]+',
		'api/title/[a-z]+'
	];

*********************
Other Helpful Methods
*********************

You will never need to use most of the methods in the Security class directly. The following are methods that
you might find helpful that are not related to the CSRF protection.

**sanitizeFilename()**

Tries to sanitize filenames in order to prevent directory traversal attempts and other security threats, which is
particularly useful for files that were supplied via user input. The first parameter is the path to sanitize.

If it is acceptable for the user input to include relative paths, e.g. file/in/some/approved/folder.txt, you can set
the second optional parameter, $relative_path to true.
::

	$path = $security->sanitizeFilename($request->getVar('filepath'));
