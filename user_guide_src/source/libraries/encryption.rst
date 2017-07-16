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
the AES-256-CBC cipher, 
using your configured *key* and SHA512 HMAC authentication.

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

Encryption ciphers
==================

A *cipher* is a combination of an algorithm, key length, and encryption mode.
For instance, "AES-256-CBC" refers to the AES algorithm using a 256 bit key and
cipher block chaining (CBC) mode.

Different encryption drivers support different sets of encryption algorithms and often implement
them in different ways. Some algorithms expect specific key lengths, while others support
variable length keys. Each algorithm usually supports several different encryption modes.

Here's a list of common ciphers:

======================== ============================ ===============================
Algorithm name           Key lengths (bits / bytes)   Supported modes
======================== ============================ ===============================
AES-128 / Rijndael-128   128 / 16                     CBC, CTR, CFB, CFB8, OFB, ECB
AES-192                  192 / 24                     CBC, CTR, CFB, CFB8, OFB, ECB
AES-256                  256 / 32                     CBC, CTR, CFB, CFB8, OFB, ECB
Blowfish                 128-448 / 16-56              CBC, CFB, OFB, ECB
CAST5 / CAST-128         88-128 / 11-16               CBC, CFB, OFB, ECB
DES                      56 / 7                       CBC, CFB, CFB8, OFB, ECB
RC4 / ARCFour            40-2048 / 5-256              Stream
TripleDES                56 / 7, 112 / 14, 168 / 21   CBC, CFB, CFB8, OFB
======================== ============================ ===============================

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

=========== =====================================================================
Mode name   Additional info
=========== =====================================================================
CBC         Cipher block chaining - a safe default choice
CFB         Cipher feedback
CTR         Counter mode
ECB         Electronic codebook - ignores IV (not recommended).
OFB         Output feedback
Stream      Not actually a mode, it just says that a stream cipher is being used.
=========== =====================================================================

OpenSSL Notes
-------------

As noted above, the encryption drivers support different sets of encryption
ciphers. The following examples are supported by OpenSSL:

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
use repeatedly the same driver, encryption cipher and key.

As noted in the "Default behavior" section above, this means using an
auto-detected driver (OpenSSL has a higher priority), the AES-256 algorithm
in CBC mode, and your ``$key`` value.

Encryption configuration settings are normally set in 
application/config/Encryption.php.
Not all settings are supported by all of the drivers

======== ===============================================
Option   Possible values (default in parentheses)
======== ===============================================
driver   Preferred handler (OpenSSL)
cipher   Cipher name (AES-256-CBC); see :ref:`ciphers-and-modes`)
key      Encryption key starter
digest   Which HMAC digest algorithm to use (SHA512)
encoding The encoding to apply to encrypted results (base64)
======== ===============================================

You can over-ride any of those settings by passing your own ``Config`` object,
or an associative array of parameters, or even just the driver name, to the Services::

    $encrypter = \Config\Services::encrypter($params);

These will replace any same-named settings in ``Config\Encryption``.

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
            'cipher' => 'aes-256-ctr',
            'key' => '<a 32-character random string>'		
	]);

Note that we only mentioned that you want to change the cipher,
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


Note that it would be easier to save these separately, if both encrypters
were to be needed as part of handling the same request.

    $encryption = new \Encryption\Encryption();
    $encrypter1 = $encryption->initialize(['driver' => 'Sodium']);;
    $encrypter2 = $encryption->initialize(['driver' => 'OpenSSL']);;

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
		:throws:	CodeIgniter\\Encryption\\EncryptionException

		Initializes (configures) the library to use different settings.

		Example::

			$encrypter = $encryption->initialize(['cipher' => '3des']);

		Please refer to the :ref:`configuration` section for detailed info.

.. php:interface:: CodeIgniter\\Encryption\\EncrypterInterface

	.. php:method:: encrypt($data)

		:param	string	$data: Data to encrypt
		:returns:	Encrypted data or FALSE on failure
		:rtype:	string

		Encrypts the input data and returns its ciphertext.

		Example::

			$ciphertext = $encrypter->encrypt('My secret message');

	.. php:method:: decrypt($data)

		:param	string	$data: Data to decrypt
		:returns:	Decrypted data or FALSE on failure
		:rtype:	string
		:throws:	CodeIgniter\\Encryption\\EncryptionException

		Decrypts the input data and returns it in plain-text.

		Example::

			echo $encrypter->decrypt($ciphertext);
