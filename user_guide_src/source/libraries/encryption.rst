##################
Encryption Service
##################

.. important:: DO NOT use this or any other *encryption* library for
	password storage! Passwords must be *hashed* instead, and you
	should do that through PHP's `Password Hashing extension
	<http://php.net/password>`_.

The Encryption Service provides two-way symmetric (secret key) data encryption. 
The service will instantiate and/or initialize an
encryption **handler** to suit your parameters as explained below.

Encryption Service handlers must implement CodeIgniter's simple ``EncrypterInterface``. 
Using an appropriate PHP cryptographic extension or third-party library may require 
additional software is installed on your server and/or might need to be explicitly 
enabled in your instance of PHP.

The following PHP extensions are currently supported:

- `OpenSSL <http://php.net/openssl>`_

This is not a full cryptographic solution. If you need more capabilities, for example, 
public-key encryption, we suggest you consider direct use of OpenSSL or 
one of the other `Cryptography Extensions <https://www.php.net/manual/en/refs.crypto.php>`_. 
A more comprehensive package like `Halite <https://github.com/paragonie/halite>`_ 
(an O-O package built on libsodium) is another possibility.

.. note:: Support for the ``MCrypt`` extension has been dropped, as that has
    been deprecated as of PHP 7.2.

.. contents::
  :local:

.. raw:: html

  <div class="custom-index container"></div>

.. _usage:

****************************
Using the Encryption Library
****************************

Like all services in CodeIgniter, it can be loaded via ``Config\Services``::

    $encrypter = \Config\Services::encrypter();

Assuming you have set your starting key (see :ref:`configuration`), 
encrypting and decrypting data is simple - pass the appropriate string to ``encrypt()`` 
and/or ``decrypt()`` methods::

	$plainText = 'This is a plain-text message!';
	$ciphertext = $encrypter->encrypt($plainText);

	// Outputs: This is a plain-text message!
	echo $encrypter->decrypt($ciphertext);

And that's it! The Encryption library will do everything necessary
for the whole process to be cryptographically secure out-of-the-box.
You don't need to worry about it.

.. _configuration:

Configuring the Library
=======================

The example above uses the configuration settings found in ``app/Config/Encryption.php``.

There are only two settings:

======== ===============================================
Option   Possible values (default in parentheses)
======== ===============================================
key      Encryption key starter
driver   Preferred handler (OpenSSL)
======== ===============================================

You can replace the config file's settings by passing a configuration 
object of your own to the ``Services`` call. The ``$config`` variable must be 
an instance of either the `Config\\Encryption` class or an object 
that extends `CodeIgniter\\Config\\BaseConfig`.
::

    $config         = new Config\Encryption();
    $config->key    = 'aBigsecret_ofAtleast32Characters';
    $config->driver = 'OpenSSL';

    $encrypter = \Config\Services::encrypter($config);


Default Behavior
================

By default, the Encryption Library uses the OpenSSL handler. That handler encrypts using 
the AES-256-CTR algorithm, your configured *key*, and SHA512 HMAC authentication.

Setting Your Encryption Key
===========================

Your encryption key **must** be as long as the encryption algorithm in use allows. 
For AES-256, that's 256 bits or 32 bytes (characters) long.

The key should be as random as possible, and it **must not** be a regular text string, 
nor the output of a hashing function, etc. To create a proper key, 
you can use the Encryption library's ``createKey()`` method.
::

	// $key will be assigned a 32-byte (256-bit) random key
	$key = Encryption::createKey(32);

The key can be stored in *app/Config/Encryption.php*, or you can design 
a storage mechanism of your own and pass the key dynamically when encrypting/decrypting.

To save your key to your *app/Config/Encryption.php*, open the file
and set::

	public $key = 'YOUR KEY';

Encoding Keys or Results
------------------------

You'll notice that the ``createKey()`` method outputs binary data, which
is hard to deal with (i.e. a copy-paste may damage it), so you may use
``bin2hex()``, ``hex2bin()`` or Base64-encoding to work with the key in
a more friendly manner. For example::

	// Get a hex-encoded representation of the key:
	$encoded = bin2hex(Encryption::createKey(32));

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

The `OpenSSL <http://php.net/openssl>`_ extension has been a standard part of PHP for a long time.

CodeIgniter's OpenSSL handler uses the AES-256-CTR cipher.

The *key* your configuration provides is used to derive two other keys, one for 
encryption and one for authentication. This is achieved by way of a technique known 
as an `HMAC-based Key Derivation Function <http://en.wikipedia.org/wiki/HKDF>`_ (HKDF).

Message Length
==============

An encrypted string is usually longer than the original, plain-text string (depending on the cipher).

This is influenced by the cipher algorithm itself, the initialization vector (IV) 
prepended to the cipher-text, and the HMAC authentication message that is also prepended.
Furthermore, the encrypted message is also Base64-encoded so that it is safe
for storage and transmission regardless of the character-set in use.

Keep this information in mind when selecting your data storage mechanism.
Cookies, for example, can only hold 4K of information.

Using the Encryption Service Directly
=====================================

Instead of (or in addition to) using ``Services`` as described in :ref:`usage`, 
you can create an "Encrypter" directly, or change the settings of an existing instance.
::

    // create an Encrypter instance
    $encryption = new \Encryption\Encryption();

    // reconfigure an instance with different settings
    $encrypter = $encryption->initialize($config);

Remember, that ``$config`` must me an instance of either a `Config\Encryption` class 
or an object that extends `CodeIgniter\Config\BaseConfig`.


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
