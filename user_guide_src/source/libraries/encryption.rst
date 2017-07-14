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

.. contents::
  :local:

.. raw:: html

  <div class="custom-index container"></div>

****************************
Using the Encryption Library
****************************

Like all services in CodeIgniter, it can be loaded via ``Config\Services``::

    $encrypter = \Config\Services::encrypter();

Default behavior
================

By default, the Encryption Library will use the OpenSSL handler, with
the AES-256 cipher in CBC mode, 
using your configured *key* and SHA512 HMAC authentication.

AES-256 is chosen both because it is proven to be strong and
because of its wide availability across different cryptographic
software and programming languages' APIs.

The *key* you provide is used for
"keyed-hash message authentication" (HMAC), which derives
two separate keys from your configured one: 
one for encryption and one for authentication. This is
done via a technique called `HMAC-based Key Derivation Function
<http://en.wikipedia.org/wiki/HKDF>`_ (HKDF).

Setting your encryption key
===========================

An *encryption key* is a piece of information that controls the
cryptographic process and permits a plain-text string to be encrypted,
and afterwards - decrypted. It is the secret "ingredient" in the whole
process that allows you to be the only one who is able to decrypt data
that you've decided to hide from the eyes of the public.
After one key is used to encrypt data, that same key provides the **only**
means to decrypt it, so not only must you chose one carefully, but you
must not lose it or you will also lose access to the data.

It must be noted that to ensure maximum security, such a key *should* not
only be as strong as possible, but also often changed. Such behavior
however is rarely practical or possible to implement, and that is why
CodeIgniter gives you the ability to configure a single key that is to be
used (almost) every time.

It goes without saying that you should guard your key carefully. Should
someone gain access to your key, the data will be easily decrypted. If
your server is not totally under your control it's impossible to ensure
key security so you may want to think carefully before using it for
anything that requires high security, like storing credit card numbers.

Your encryption key **must** be as long as the encryption algorithm in use
allows. For AES-256, that's 256 bits or 32 bytes (characters) long.
You will find a table below that shows the supported key lengths of
different ciphers.

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

You'll notice that the ``createKey()`` method outputs binary data, which
is hard to deal with (i.e. a copy-paste may damage it), so you may use
``bin2hex()``, ``hex2bin()`` or Base64-encoding to work with the key in
a more friendly manner. For example::

	// Get a hex-encoded representation of the key:
	$encoded = bin2hex($encrypter->createKey(32));

	// Put the same value in your config with hex2bin(),
	// so that it is still passed as binary to the library:
	$key = hex2bin(<your hex-encoded key>);

.. _ciphers-and-modes:

Encryption ciphers and modes
============================

.. note:: The terms 'cipher' and 'encryption algorithm' are interchangeable.

Different encryption drivers support different sets of encryption algorithms and often implement
them in different ways. Some algorithms expect specific key lengths, while others support
variable length keys. Each algorithm usually supports several different encryption modes.

Here's a list of common ciphers:

======================== ============================ ===============================
Cipher name              Key lengths (bits / bytes)   Supported modes
======================== ============================ ===============================
AES-128 / Rijndael-128   128 / 16                     CBC, CTR, CFB, CFB8, OFB, ECB
AES-192                  192 / 24                     CBC, CTR, CFB, CFB8, OFB, ECB
AES-256                  256 / 32                     CBC, CTR, CFB, CFB8, OFB, ECB
Blowfish                 128-448 / 16-56              CBC, CFB, OFB, ECB
CAST5 / CAST-128         88-128 / 11-16               CBC, CFB, OFB, ECB
DES                      56 / 7                       CBC, CFB, CFB8, OFB, ECB
RC4 / ARCFour            40-2048 / 5-256              Stream
TripleDES                56 / 7, 112 / 14, 168 / 21   CBC, CFB, CFB8, OFB
======================== ================== ============================ ===============================

.. note:: Blowfish, CAST5 and RC4 support variable length keys, 
        although in bit terms that only happens in 8-bit increments.

        Even though CAST5 supports key lengths lower than 128 bits
	(16 bytes), in fact they will just be zero-padded to the
	maximum length, as specified in `RFC 2144
	<http://tools.ietf.org/rfc/rfc2144.txt>`_.

.. _encryption-modes:

Encryption modes
----------------

Different modes of encryption have different characteristics and serve
different purposes. Some are stronger than others, some are faster
and some offer extra features.
If you are unsure which to use, stick to the CBC mode - it is widely accepted 
as strong and secure for general purposes.

=========== ===================================================================================================================================================
Mode name   Additional info
=========== ===================================================================================================================================================
CBC         A safe default choice
CFB         N/A
CFB8        Same as CFB, but operates in 8-bit mode (not recommended).
CTR         Considered as theoretically better than CBC, but not as widely available
ECB         Ignores IV (not recommended).
OFB         N/A
XTS         Usually used for encrypting random access data such as RAM or hard-disk storage.
Stream      This is not actually a mode, it just says that a stream cipher is being used. Required because of the general cipher+mode initialization process.
=========== ===================================================================================================================================================

OpenSSL Notes
-------------

As noted above, the encryption drivers support different sets of encryption
ciphers. We do recommend that you use driver-specific settings.

The following are supported by OpenSSL:

============== ============================== =========================================
Cipher name    Key lengths (bits / bytes)     Supported modes
============== ============================== =========================================
AES-128        128 / 16                       CBC, CTR, CFB, CFB8, OFB, ECB, XTS
AES-192        192 / 24                       CBC, CTR, CFB, CFB8, OFB, ECB, XTS
AES-256        256 / 32                       CBC, CTR, CFB, CFB8, OFB, ECB, XTS
Blowfish       128-448 / 16-56                CBC, CFB, OFB, ECB
Camellia-128   128 / 16                       CBC, CFB, CFB8, OFB, ECB
Camellia-192   192 / 24                       CBC, CFB, CFB8, OFB, ECB
Camellia-256   256 / 32                       CBC, CFB, CFB8, OFB, ECB
CAST5          88-128 / 11-16                 CBC, CFB, OFB, ECB
DES            56 / 7                         CBC, CFB, CFB8, OFB, ECB
RC2            8-1024 / 1-128                 CBC, CFB, OFB, ECB
RC4            40-2048 / 5-256                Stream
TripleDES      56 / 7, 112 / 14, 168 / 21     CBC, CFB, CFB8, OFB
Seed           128 / 16                       CBC, CFB, OFB, ECB
============== ============================== =========================================


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

.. _configuration:

Configuring the library
=======================

The Encryption library is designed to
use repeatedly the same driver, encryption cipher, mode and key.

As noted in the "Default behavior" section above, this means using an
auto-detected driver (OpenSSL has a higher priority), the AES-256 ciper
in CBC mode, and your ``$key`` value.

Encryption configuration settings are normally set in 
application/config/Encryption.php.

======== ===============================================
Option   Possible values
======== ===============================================
driver   Preferred handler: 'OpenSSL'
cipher   Cipher name (see :ref:`ciphers-and-modes`)
mode     Encryption mode (see :ref:`encryption-modes`)
key      Encryption key 
======== ===============================================

You can over-ride any of those settings by passing your own ``Config`` object,
or an associative array of parameters, to the Services::

    $encrypter = \Config\Services::encrypter($params);

These will replace any same-named settings in ``Config\Encryption``.

Using the Encryption manager directly
=====================================

Instead of, or in addition to, using the `Services` described
at the beginning of this page, you can use the encryption manager
directly, to create an ``Encrypter`` or to change the settings
of the current one.

    $encryption = new \Encryption\Encryption();
    $encrypter = $encryption->initialize($params);

For example, if you were to change the encryption algorithm and
mode to AES-256 in CTR mode, this is what you should do::

    $encryption = new \Encryption\Encryption();
    $encrypter = $encryption->initialize([
            'cipher' => 'aes-256',
            'mode' => 'ctr',
            'key' => '<a 32-character random string>'		
	]);

Note that we only mentioned that you want to change the cipher and mode,
but we also included a key in the example. As previously noted, it is
important that you choose a key with a proper size for the used algorithm.

If you want to change the driver, for instance switching between
Sodium and OpenSSL, you could go through the Services::

	// Switch to the Sodium driver
	$encrypter= \Config\Services::encrypter(['driver' => 'Sodium']);;
        // encrypt data using Sodium

	// Switch back to the OpenSSL driver
	$encrypter= \Config\Services::encrypter(['driver' => 'OpenSSL']);;
        // now encrypt data using OpenSSL

Alternately, you could use the encryption manager directly:

    $encryption = new \Encryption\Encryption();

    // Switch to the Sodium driver
    $encrypter= $encryption->initialize(['driver' => 'Sodium']);;
    // encrypt data using Sodium

    // Switch back to the OpenSSL driver
    $encrypter= $encryption->initialize(['driver' => 'OpenSSL']);;
    // now encrypt data using OpenSSL

Encrypting and decrypting data
==============================

Encrypting and decrypting data with the already configured library
settings is simple - pass the appropriate string to the
``encrypt()`` and/or ``decrypt()`` methods::

	$plain_text = 'This is a plain-text message!';
	$ciphertext = $encrypter->encrypt($plaintext);

	// Outputs: This is a plain-text message!
	echo $encrypter->decrypt($ciphertext);

And that's it! The Encryption library will do everything necessary
for the whole process to be cryptographically secure out-of-the-box.
You don't need to worry about it.

.. important:: Both methods will return FALSE in case of an error.
	While for ``encrypt()`` this can only mean incorrect
	configuration, you should always check the return value
	of ``decrypt()`` in production code.


.. _custom-parameters:

Using custom parameters
-----------------------

If you have to interact with another system that is out
of your control and uses another method to encrypt data,
you can change how the encryption
and decryption processes work, so that you can easily tailor a
custom solution for such situations.

.. note:: It is possible to use the library in this way, without
	setting an *encryption_key* in your configuration file.

All you have to do is to pass an associative array with a few
parameters to either the ``encrypt()`` or ``decrypt()`` method.
Here's an example::

	// Assume that we have $ciphertext, $key and $hmac_key
	// from on outside source
	$message = $encrypter->decrypt(
		$ciphertext,
		array(
			'cipher' => 'blowfish',
			'mode' => 'cbc',
			'key' => $key,
			'hmac_digest' => 'sha256',
			'hmac_key' => $hmac_key
		)
	);

In the above example, we are decrypting a message that was encrypted
using the Blowfish cipher in CBC mode and authenticated via a SHA-256
HMAC.

.. important:: Note that both 'key' and 'hmac_key' are used in this
	example. When using custom parameters, encryption and HMAC keys
	are not derived like the default behavior of the library is.

Below is a list of the available options for ``encrypt()`` and ``decrypt``.
Unless you really need to do this, and you know what you are doing,
we advise you to not change the encryption process as this could
impact security.

============= =============== ============================= ======================================================
Option        Default value   Mandatory / Optional          Description
============= =============== ============================= ======================================================
cipher        N/A             Yes                           Encryption algorithm (see :ref:`ciphers-and-modes`).
mode          N/A             Yes                           Encryption mode (see :ref:`encryption-modes`).
key           N/A             Yes                           Encryption key.
hmac          TRUE            No                            Whether to use a HMAC.
                                                            Boolean. If set to FALSE, then *hmac_digest* and
                                                            *hmac_key* will be ignored.
hmacDigest    sha512          No                            HMAC message digest algorithm (see :ref:`digests`).
hmacKey       N/A             Yes, unless *hmac* is FALSE   HMAC key.
rawdata       FALSE           No                            Whether the ciphertext should be raw.
                                                            Boolean. If set to TRUE, then Base64 encoding and
                                                            decoding will not be performed and HMAC will not
                                                            be a hexadecimal string.
============= =============== ============================= ======================================================

.. important:: ``encrypt()`` and ``decrypt()`` will return FALSE if
	a mandatory parameter is not provided or if a provided
	value is incorrect. This includes *hmacKey*, unless *hmac*
	is set to FALSE.

.. _digests:

Supported HMAC authentication algorithms
----------------------------------------

For HMAC message authentication, the Encryption library supports
usage of the SHA-2 family of algorithms:

=========== ==================== ============================
Algorithm   Raw length (bytes)   Hex-encoded length (bytes)
=========== ==================== ============================
sha512      64                   128
sha384      48                   96
sha256      32                   64
sha224      28                   56
=========== ==================== ============================

The reason for not including other popular algorithms, such as
MD5 or SHA1 is that they are no longer considered secure enough
and as such, we don't want to encourage their usage.
If you absolutely need to use them, it is easy to do so via PHP's
native `hash_hmac() <http://php.net/manual/en/function.hash-hmac.php>`_ function.

Stronger algorithms of course will be added in the future as they
appear and become widely available.

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


	.. php:method:: initialize($params)

		:param	array	$params: Configuration parameters
		:returns:	CodeIgniter\\Encryption\\EncrypterInterface instance (for method chaining)
		:rtype:	CodeIgniter\\Encryption\\EncrypterInterface

		Initializes (configures) the library to use a different
		driver, cipher, mode or key.

		Example::

			$encrypter = $encryption->initialize(['mode' => 'ctr']);

		Please refer to the :ref:`configuration` section for detailed info.

.. php:interface:: CodeIgniter\\Encryption\\EncrypterInterface

	.. php:method:: encrypt($data[, $params = NULL])

		:param	string	$data: Data to encrypt
		:param	array	$params: Optional parameters
		:returns:	Encrypted data or FALSE on failure
		:rtype:	string

		Encrypts the input data and returns its ciphertext.

		Example::

			$ciphertext = $encrypter->encrypt('My secret message');

		Please refer to the :ref:`custom-parameters` section for information
		on the optional parameters.

	.. php:method:: decrypt($data[, $params = NULL])

		:param	string	$data: Data to decrypt
		:param	array	$params: Optional parameters
		:returns:	Decrypted data or FALSE on failure
		:rtype:	string

		Decrypts the input data and returns it in plain-text.

		Example::

			echo $encrypter->decrypt($ciphertext);

		Please refer to the :ref:`custom-parameters` secrion for information
		on the optional parameters.

	.. php:method:: hkdf($key[, $digest = 'sha512'[, $salt = NULL[, $length = NULL[, $info = '']]]])

		:param	string	$key: Input key material
		:param	string	$digest: A SHA-2 family digest algorithm
		:param	string	$salt: Optional salt
		:param	int	$length: Optional output length
		:param	string	$info: Optional context/application-specific info
		:returns:	A pseudo-random key or FALSE on failure
		:rtype:	string

		Derives a key from another, presumably weaker key.

		This method is used internally to derive an encryption and HMAC key
		from your configured *encryption_key*.

		It is publicly available due to its otherwise general purpose. It is
		described in `RFC 5869 <https://tools.ietf.org/rfc/rfc5869.txt>`_.

		However, as opposed to the description in RFC 5869, this implementation
		doesn't support SHA1.

		Example::

			$hmacKey = $encrypter->hkdf(
				$key,
				'sha512',
				NULL,
				NULL,
				'authentication'
			);

			// $hmacKey is a pseudo-random key with a length of 64 bytes