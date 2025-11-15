##################
Encryption Service
##################

.. important:: DO NOT use this or any other *encryption* library for
    password storage! Passwords must be *hashed* instead, and you
    should do that through PHP's `Password Hashing extension
    <https://www.php.net/password>`_.

The Encryption Service provides two-way symmetric (secret key) data encryption.
The service will instantiate and/or initialize an
encryption **handler** to suit your parameters as explained below.

Encryption Service handlers must implement CodeIgniter's simple ``EncrypterInterface``.
Using an appropriate PHP cryptographic extension or third-party library may require
additional software to be installed on your server and/or might need to be explicitly
enabled in your instance of PHP.

The following PHP extensions are currently supported:

- `OpenSSL <https://www.php.net/openssl>`_
- `Sodium <https://www.php.net/manual/en/book.sodium>`_

This is not a full cryptographic solution. If you need more capabilities, for example,
public-key encryption, we suggest you consider direct use of OpenSSL or
one of the other `Cryptography Extensions <https://www.php.net/manual/en/refs.crypto.php>`_.
A more comprehensive package like `Halite <https://github.com/paragonie/halite>`_
(an O-O package built on libsodium) is another possibility.

.. note:: Support for the ``MCrypt`` extension has been dropped, as that has
    been deprecated as of PHP 7.2.

.. contents::
    :local:
    :depth: 3

.. _usage:

****************************
Using the Encryption Library
****************************

Like all services in CodeIgniter, it can be loaded via ``Config\Services``:

.. literalinclude:: encryption/001.php

Assuming you have set your starting key (see :ref:`configuration`),
encrypting and decrypting data is simple - pass the appropriate string to ``encrypt()``
and/or ``decrypt()`` methods:

.. literalinclude:: encryption/002.php

And that's it! The Encryption library will do everything necessary
for the whole process to be cryptographically secure out-of-the-box.
You don't need to worry about it.

.. _configuration:

Configuring the Library
=======================

The example above uses the configuration settings found in **app/Config/Encryption.php**.

============== ==========================================================================
Option         Possible values (default in parentheses)
============== ==========================================================================
key            Encryption key starter
driver         Preferred handler, e.g., OpenSSL or Sodium (``OpenSSL``)
digest         Message digest algorithm (``SHA512``)
blockSize      [**SodiumHandler** only] Padding length in bytes (``16``)
cipher         [**OpenSSLHandler** only] Cipher to use (``AES-256-CTR``)
encryptKeyInfo [**OpenSSLHandler** only] Encryption key info (``''``)
authKeyInfo    [**OpenSSLHandler** only] Authentication key info (``''``)
rawData        [**OpenSSLHandler** only] Whether the cipher-text should be raw (``true``)
============== ==========================================================================

You can replace the config file's settings by passing a configuration
object of your own to the ``Services`` call. The ``$config`` variable must be
an instance of the ``Config\Encryption`` class.

.. literalinclude:: encryption/003.php

.. _encryption-compatible-with-ci3:

Configuration to Maintain Compatibility with CI3
------------------------------------------------

.. versionadded:: 4.3.0

Since v4.3.0, you can decrypt data encrypted with CI3's Encryption.
If you need to decrypt such data, use the following settings to maintain compatibility.

.. literalinclude:: encryption/013.php

Supported HMAC Authentication Algorithms
----------------------------------------

For HMAC message authentication, the Encryption library supports
usage of the SHA-2 family of algorithms:

=========== ==================== ============================
Algorithm   Raw length (bytes)   Hex-encoded length (bytes)
=========== ==================== ============================
SHA512      64                   128
SHA384      48                   96
SHA256      32                   64
SHA224      28                   56
=========== ==================== ============================

The reason for not including other popular algorithms, such as
MD5 or SHA1 is that they are no longer considered secure enough
and as such, we don't want to encourage their usage.
If you absolutely need to use them, it is easy to do so via PHP's
native `hash_hmac() <http://php.net/manual/en/function.hash-hmac.php>`_ function.

Stronger algorithms of course will be added in the future as they
appear and become widely available.

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

.. literalinclude:: encryption/004.php

The key can be stored in **app/Config/Encryption.php**, or you can design
a storage mechanism of your own and pass the key dynamically when encrypting/decrypting.

To save your key to your **app/Config/Encryption.php**, open the file
and set:

.. literalinclude:: encryption/005.php

Encoding Keys or Results
------------------------

You'll notice that the ``createKey()`` method outputs binary data, which
is hard to deal with (i.e., a copy-paste may damage it), so you may use
``bin2hex()``, or ``base64_encode`` to work with the key in
a more friendly manner. For example:

.. literalinclude:: encryption/006.php

You might find the same technique useful for the results
of encryption:

.. literalinclude:: encryption/007.php

Using Prefixes in Storing Keys
------------------------------

You may take advantage of two special prefixes in storing your
encryption keys: ``hex2bin:`` and ``base64:``. When these prefixes
immediately precede the value of your key, ``Encryption`` will
intelligently parse the key and still pass a binary string to
the library.

.. literalinclude:: encryption/008.php

Similarly, you can use these prefixes in your **.env** file, too!
::

    // For hex2bin
    encryption.key = hex2bin:<your-hex-encoded-key>

    // or
    encryption.key = base64:<your-base64-encoded-key>

Padding
=======

Sometimes, the length of a message may provide a lot of information about its nature. If
a message is one of "yes", "no" and "maybe", encrypting the message doesn't help: knowing
the length is enough to know what the message is.

Padding is a technique to mitigate this, by making the length a multiple of a given block size.

Padding is implemented in ``SodiumHandler`` using libsodium's native ``sodium_pad`` and ``sodium_unpad``
functions. This requires the use of a padding length (in bytes) that is added to the plaintext
message prior to encryption, and removed after decryption. Padding is configurable via the
``$blockSize`` property of ``Config\Encryption``. This value should be greater than zero.

.. important:: You are advised not to devise your own padding implementation. You must always use
    the more secure implementation of a library. Also, passwords should not be padded. Usage of
    padding in order to hide the length of a password is not recommended. A client willing to send
    a password to a server should hash it instead (even with a single iteration of the hash function).
    This ensures that the length of the transmitted data is constant, and that the server doesn't
    effortlessly get a copy of the password.

Encryption Handler Notes
========================

OpenSSL Notes
-------------

The `OpenSSL <https://www.php.net/openssl>`_ extension has been a standard part of PHP for a long time.

CodeIgniter's OpenSSL handler uses the AES-256-CTR cipher.

The *key* your configuration provides is used to derive two other keys, one for
encryption and one for authentication. This is achieved by way of a technique known
as an `HMAC-based Key Derivation Function <https://en.wikipedia.org/wiki/HKDF>`_ (HKDF).

Sodium Notes
------------

The `Sodium <https://www.php.net/manual/en/book.sodium>`_ extension is bundled by default in PHP as
of PHP 7.2.0.

Sodium uses the algorithms XSalsa20 to encrypt, Poly1305 for MAC, and XS25519 for key exchange in
sending secret messages in an end-to-end scenario. To encrypt and/or authenticate a string using
a shared-key, such as symmetric encryption, Sodium uses the XSalsa20 algorithm to encrypt and
HMAC-SHA512 for the authentication.

.. note:: CodeIgniter's ``SodiumHandler`` uses ``sodium_memzero`` in every encryption or decryption
    session. After each session, the message (whether plaintext or ciphertext) and starter key are
    wiped out from the buffers. You may need to provide again the key before starting a new session.

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

.. literalinclude:: encryption/009.php

Remember, that ``$config`` must be an instance of ``Config\Encryption`` class.

***************
Class Reference
***************

.. php:namespace:: CodeIgniter\Encryption

.. php:class:: Encryption

    .. php:staticmethod:: createKey([$length = 32])

        :param int $length: Output length
        :returns: A pseudo-random cryptographic key with the specified length, or ``false`` on failure
        :rtype:    string

        Creates a cryptographic key by fetching random data from
        the operating system's sources (*i.e.* ``/dev/urandom``).

    .. php:method:: initialize([Encryption $config = null])

        :param Config\\Encryption $config: Configuration parameters
        :returns: ``CodeIgniter\Encryption\EncrypterInterface`` instance
        :rtype:    ``CodeIgniter\Encryption\EncrypterInterface``
        :throws: ``CodeIgniter\Encryption\Exceptions\EncryptionException``

        Initializes (configures) the library to use different settings.

        Example:

        .. literalinclude:: encryption/010.php

        Please refer to the :ref:`configuration` section for detailed info.

.. php:interface:: CodeIgniter\Encryption\EncrypterInterface

    .. php:method:: encrypt($data[, $params = null])

        :param string $data: Data to encrypt
        :param array|string|null $params: Configuration parameters (key)
        :returns: Encrypted data
        :rtype:    string
        :throws: ``CodeIgniter\Encryption\Exceptions\EncryptionException``

        Encrypts the input data and returns its ciphertext.

        If you pass parameters as the second argument, the ``key`` element
        will be used as the starting key for this operation if ``$params``
        is an array; or the starting key may be passed as a string.

        If you are using the SodiumHandler and want to pass a different ``blockSize``
        on runtime, pass the ``blockSize`` key in the ``$params`` array.

        Examples:

        .. literalinclude:: encryption/011.php

    .. php:method:: decrypt($data[, $params = null])

        :param string $data: Data to decrypt
        :param array|string|null $params: Configuration parameters (key)
        :returns: Decrypted data
        :rtype:    string
        :throws: ``CodeIgniter\Encryption\Exceptions\EncryptionException``

        Decrypts the input data and returns it in plain-text.

        If you pass parameters as the second argument, the ``key`` element
        will be used as the starting key for this operation if ``$params``
        is an array; or the starting key may be passed as a string.

        If you are using the SodiumHandler and want to pass a different ``blockSize``
        on runtime, pass the ``blockSize`` key in the ``$params`` array.

        Examples:

        .. literalinclude:: encryption/012.php
