##################
Encryption Service
##################

.. important:: DO NOT use this or any other *encryption* library for
	user password storage! Passwords must be *hashed* instead, and you
	should do that via PHP's own `Password Hashing extension
	<http://php.net/password>`_.

The Encryption Service provides two-way symmetric (secret key) data encryption. 
The encryption manager will instantiate and/or initialize an
encryption handler to suit your parameters, explained below.

The handlers adapt our simple ``EncrypterInterface`` to use an
appropriate PHP cryptographic extension or third party library.
Such extensions may need to be explicitly enabled in your instance of PHP.

The following extensions are currently supported:

- `OpenSSL <http://php.net/openssl>`_

.. note:: Support for the ``MCrypt`` extension has been dropped, as that has
    been deprecated as of PHP 7.2.

This is not a full cryptographic solution. If you need more capabilities,
for instance public key encryption, we suggest you consider using the
above extensions directly, or look into some of the more comprehensive
packages, like:

- `Halite <https://github.com/paragonie/halite>`_, an O-O package built on libsodium, or
- `Sodium_compat <https://github.com/paragonie/sodium_compat>`_, a pure PHP implementation that adds libsodium support to earlier versions of PHP (5.2.4+)

.. contents::
  :local:

.. raw:: html

  <div class="custom-index container"></div>

****************************
Using the Encryption Library
****************************

Like all services in CodeIgniter, it can be loaded via ``Config\Services``::

    $encrypter = \Config\Services::encrypter();

Assuming you have set your starting key (see below), 
encrypting and decrypting data is simple - pass the appropriate string to the
``encrypt()`` and/or ``decrypt()`` methods::

	$plain_text = 'This is a plain-text message!';
	$ciphertext = $encrypter->encrypt($plaintext);

	// Outputs: This is a plain-text message!
	echo $encrypter->decrypt($ciphertext);

And that's it! The Encryption library will do everything necessary
for the whole process to be cryptographically secure out-of-the-box.
You don't need to worry about it.

.. _configuration:

Configuring the library
=======================

The example above uses the default configuration settings,
found in ``application/config/Encryption.php``.

There are only two settings:

======== ===============================================
Option   Possible values (default in parentheses)
======== ===============================================
driver   Preferred handler (OpenSSL)
key      Encryption key starter
======== ===============================================

You can over-ride any of these settings by passing your own ``Config`` object
to the Services::

    $encrypter = \Config\Services::encrypter($config);

Default behavior
================

By default, the Encryption Library will use the OpenSSL handler, with
the AES-256-CTR cipher, 
using your configured *key* and SHA512 HMAC authentication.

The *key* you provide is used to derive
two separate keys from your configured one: 
one for encryption and one for authentication. This is
done via a technique called `HMAC-based Key Derivation Function
<http://en.wikipedia.org/wiki/HKDF>`_ (HKDF).

Setting your encryption key
===========================

Your encryption key **must** be as long as the encryption algorithm in use
allows. For AES-256, that's 256 bits or 32 bytes (characters) long.

The key should be as random as possible and it **must not** be a regular
text string, nor the output of a hashing function, etc. In order to create
a proper key, you can use the Encryption library's ``createKey()`` method
::

	// $key will be assigned a 32-byte (256-bit) random key
	$key = Encryption::createKey(32);

The key can be either stored in your *application/Config/Encryption.php*, or
you can design your own storage mechanism and pass the key dynamically
when encrypting/decrypting.

To save your key to your *application/Config/Encryption.php*, open the file
and set::

	$key = 'YOUR KEY';

Encoding Keys or Results
------------------------

You'll notice that the ``createKey()`` method outputs binary data, which
is hard to deal with (i.e. a copy-paste may damage it), so you may use
``bin2hex()``, ``hex2bin()`` or Base64-encoding to work with the key in
a more friendly manner. For example::

	// Get a hex-encoded representation of the key:
	$encoded = bin2hex($encrypter->createKey(32));

	// Put the same value in your config with hex2bin(),
	// so that it is still passed as binary to the library:
	$key = hex2bin(<your hex-encoded key>);

You might find the same technique useful for the results
of encryption::

	// Encrypt some text & make the results text
	$encoded = base64_encode($encrypter->encrypt($plaintext));

Encryption Handler Notes
========================

OpenSSL Notes
-------------

OpenSSL has been a standard part of PHP for some time.

The OpenSSL handler uses the AES-256-CTR cipher.

Message Length
==============

An encrypted string is usually
longer than the original, plain-text string (depending on the cipher).

This is influenced by the cipher algorithm itself, the initialization vector (IV) 
prepended to the
cipher-text and the HMAC authentication message that is also prepended.
Furthermore, the encrypted message is also Base64-encoded so that it is safe
for storage and transmission, regardless of a possible character set in use.

Keep this information in mind when selecting your data storage mechanism.
Cookies, for example, can only hold 4K of information.

Using the Encryption manager directly
=====================================

Instead of, or in addition to, using the `Services` described
at the beginning of this page, you can use the encryption manager
directly, to create an ``Encrypter`` or to change the settings
of the current one::

    $encryption = new \Encryption\Encryption();
    $encrypter = $encryption->initialize($config);


***************
Class Reference
***************

.. php:class:: CodeIgniter\\Encryption\\Encryption

	.. php:staticmethod:: createKey($length)

		:param	int	$length: Output length
		:returns:	A pseudo-random cryptographic key with the specified length, or FALSE on failure
		:rtype:	string

		Creates a cryptographic key by fetching random data from
		the operating system's sources (i.e. /dev/urandom).


	.. php:method:: initialize($config)

		:param	BaseConfig	$config: Configuration parameters
		:returns:	CodeIgniter\\Encryption\\EncrypterInterface instance
		:rtype:	CodeIgniter\\Encryption\\EncrypterInterface
		:throws:	CodeIgniter\\Encryption\\EncryptionException

		Initializes (configures) the library to use different settings.

		Example::

			$encrypter = $encryption->initialize(['cipher' => '3des']);

		Please refer to the :ref:`configuration` section for detailed info.

.. php:interface:: CodeIgniter\\Encryption\\EncrypterInterface

	.. php:method:: encrypt($data, $params = null)

		:param	string	$data: Data to encrypt
		:param		$params: Configuration parameters (key)
		:returns:	Encrypted data or FALSE on failure
		:rtype:	string
		:throws:	CodeIgniter\\Encryption\\EncryptionException

		Encrypts the input data and returns its ciphertext.

                If you pass parameters as the second argument, the ``key`` element
                will be used as the starting key for this operation if ``$params``
                is an array; or the starting key may be passed as a string.

		Examples::

			$ciphertext = $encrypter->encrypt('My secret message');
			$ciphertext = $encrypter->encrypt('My secret message', ['key' => 'New secret key']);
			$ciphertext = $encrypter->encrypt('My secret message', 'New secret key');

	.. php:method:: decrypt($data, $params = null)

		:param	string	$data: Data to decrypt
		:param		$params: Configuration parameters (key)
		:returns:	Decrypted data or FALSE on failure
		:rtype:	string
		:throws:	CodeIgniter\\Encryption\\EncryptionException

		Decrypts the input data and returns it in plain-text.

                If you pass parameters as the second argument, the ``key`` element
                will be used as the starting key for this operation if ``$params``
                is an array; or the starting key may be passed as a string.


		Examples::

			echo $encrypter->decrypt($ciphertext);
			echo $encrypter->decrypt($ciphertext, ['key' => 'New secret key']);
			echo $encrypter->decrypt($ciphertext, 'New secret key');
