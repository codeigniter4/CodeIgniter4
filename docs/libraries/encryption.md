# Encryption Service

!!! important "Important"
    DO NOT use this or any other *encryption* library for password storage! Passwords must be *hashed* instead, and you should do that through PHP's [Password Hashing extension](https://www.php.net/password).

The Encryption Service provides two-way symmetric (secret key) data encryption. The service will instantiate and/or initialize an encryption **handler** to suit your parameters as explained below.

Encryption Service handlers must implement CodeIgniter's simple `EncrypterInterface`. Using an appropriate PHP cryptographic extension or third-party library may require additional software to be installed on your server and/or might need to be explicitly enabled in your instance of PHP.

The following PHP extensions are currently supported:

- [OpenSSL](https://www.php.net/openssl)
- [Sodium](https://www.php.net/manual/en/book.sodium)

This is not a full cryptographic solution. If you need more capabilities, for example, public-key encryption, we suggest you consider direct use of OpenSSL or one of the other [Cryptography Extensions](https://www.php.net/manual/en/refs.crypto.php). A more comprehensive package like [Halite](https://github.com/paragonie/halite) (an O-O package built on libsodium) is another possibility.

!!! note "Note"
    Support for the `MCrypt` extension has been dropped. PHP deprecated it in 7.1 and removed it from the core in 7.2.

## Using the Encryption Library

Like all services in CodeIgniter, it can be loaded via `Config\Services`:

```php
<?php

$encrypter = service('encrypter');
```

Assuming you have set your starting key (see [Configuring the Library](#configuring-the-library)), encrypting and decrypting data is simple - pass the appropriate string to the `encrypt()` and/or `decrypt()` methods:

```php
<?php

$plainText  = 'This is a plain-text message!';
$ciphertext = $encrypter->encrypt($plainText);

// Outputs: This is a plain-text message!
echo $encrypter->decrypt($ciphertext);
```

And that's it! The Encryption library will do everything necessary for the whole process to be cryptographically secure out of the box. You don't need to worry about it.

### Configuring the Library

The example above uses the configuration settings found in **app/Config/Encryption.php**.

| Option | Possible values (default in parentheses) |
|----|----|
| key | Encryption key starter |
| driver | Preferred handler, e.g., OpenSSL or Sodium (`OpenSSL`) |
| digest | Message digest algorithm (`SHA512`) |
| blockSize | [**SodiumHandler** only] Padding length in bytes (`16`) |
| cipher | [**OpenSSLHandler** only] Cipher to use (`AES-256-CTR`) |
| encryptKeyInfo | [**OpenSSLHandler** only] Encryption key info (`''`) |
| authKeyInfo | [**OpenSSLHandler** only] Authentication key info (`''`) |
| rawData | [**OpenSSLHandler** only] Whether the cipher-text should be raw (`true`) |

You can replace the config file's settings by passing a configuration object of your own to the `Services` call. The `$config` variable must be an instance of the `Config\Encryption` class.

```php
<?php

use Config\Encryption;

$config         = config(Encryption::class);
$config->key    = 'aBigsecret_ofAtleast32Characters';
$config->driver = 'OpenSSL';

$encrypter = service('encrypter', $config);
```

#### Configuration to Maintain Compatibility with CI3

!!! success "Available from version 4.3.0"

Since v4.3.0, you can decrypt data encrypted with CI3's Encryption. If you need to decrypt such data, use the following settings to maintain compatibility.

```php
<?php

use Config\Encryption;

$config         = new Encryption();
$config->driver = 'OpenSSL';

// Your CI3's 'encryption_key'
$config->key = hex2bin('64c70b0b8d45b80b9eba60b8b3c8a34d0193223d20fea46f8644b848bf7ce67f');
// Your CI3's 'cipher' and 'mode'
$config->cipher = 'AES-128-CBC';

$config->rawData        = false;
$config->encryptKeyInfo = 'encryption';
$config->authKeyInfo    = 'authentication';

$encrypter = service('encrypter', $config);
```

#### Supported HMAC Authentication Algorithms

For HMAC message authentication, the Encryption library supports usage of the SHA-2 family of algorithms:

| Algorithm | Raw length (bytes) | Hex-encoded length (bytes) |
|-----------|--------------------|----------------------------|
| SHA512    | 64                 | 128                        |
| SHA384    | 48                 | 96                         |
| SHA256    | 32                 | 64                         |
| SHA224    | 28                 | 56                         |

The reason for not including other popular algorithms, such as MD5 or SHA1, is that they are no longer considered secure enough and as such, we don't want to encourage their usage. If you absolutely need to use them, it is easy to do so via PHP's native [hash_hmac()](http://php.net/manual/en/function.hash-hmac.php) function.

Stronger algorithms will, of course, be added in the future as they appear and become widely available.

### Default Behavior

By default, the Encryption Library uses the OpenSSL handler. That handler encrypts using the AES-256-CTR algorithm, your configured *key*, and SHA512 HMAC authentication.

### Setting Your Encryption Key

Your encryption key **must** be as long as the encryption algorithm in use allows. For AES-256, that's 256 bits or 32 bytes (characters) long.

The key should be as random as possible, and it **must not** be a regular text string, nor the output of a hashing function, etc. To create a proper key, you can use the Encryption library's `createKey()` method.

```php
<?php

// $key will be assigned a 32-byte (256-bit) random key
$key = \CodeIgniter\Encryption\Encryption::createKey();

// for the SodiumHandler, you can use either:
$key = sodium_crypto_secretbox_keygen();
$key = \CodeIgniter\Encryption\Encryption::createKey(SODIUM_CRYPTO_SECRETBOX_KEYBYTES);
```

The key can be stored in **app/Config/Encryption.php**, or you can design a storage mechanism of your own and pass the key dynamically when encrypting/decrypting.

To save your key to your **app/Config/Encryption.php**, open the file and set:

```php
<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Encryption extends BaseConfig
{
    public $key = 'YOUR KEY';

    // ...
}
```

#### Encoding Keys or Results

You'll notice that the `createKey()` method outputs binary data, which is hard to deal with (i.e., a copy-paste may damage it), so you may use `bin2hex()` or `base64_encode` to work with the key in a more friendly manner. For example:

```php
<?php

// Get a hex-encoded representation of the key:
$encoded = bin2hex(\CodeIgniter\Encryption\Encryption::createKey(32));

// Put the same value with hex2bin(),
// so that it is still passed as binary to the library:
$key = hex2bin('your-hex-encoded-key');
```

You might find the same technique useful for the results of encryption:

```php
<?php

// Encrypt some text & make the results text
$encoded = base64_encode($encrypter->encrypt($plaintext));
```

#### Using Prefixes in Storing Keys

You may take advantage of two special prefixes when storing your encryption keys: `hex2bin:` and `base64:`. When these prefixes immediately precede the value of your key, `Encryption` will intelligently parse the key and still pass a binary string to the library.

```php
<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Encryption extends BaseConfig
{
    // In Encryption, you may use
    public $key = 'hex2bin:<your-hex-encoded-key>';
    // or
    public $key = 'base64:<your-base64-encoded-key>';
    // ...
}
```

Similarly, you can use these prefixes in your **.env** file, too!

```
// For hex2bin
encryption.key = hex2bin:<your-hex-encoded-key>

// or
encryption.key = base64:<your-base64-encoded-key>
```

### Encryption Key Rotation

!!! success "Available from version 4.7.0"

When you need to rotate your encryption key (for security best practices or compliance requirements), you can use the `previousKeys` configuration option to maintain the ability to decrypt data encrypted with old keys while using a new key for all new encryption operations.

#### How It Works

- **Encryption** always uses the current `key` value
- **Decryption** tries the current `key` first
- If decryption fails, it automatically falls back to trying each key in `previousKeys`
- This allows seamless key rotation without data loss

#### Configuration

Add your old keys to the `$previousKeys` property in **app/Config/Encryption.php**:

```php
<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Encryption extends BaseConfig
{
    public string $key = 'hex2bin:your_new_encryption_key_in_hex';

    public array|string $previousKeys = [
        'hex2bin:your_old_encryption_key_in_hex',
        'hex2bin:another_old_key_if_needed',
    ];

    // ... other config options
}
```

#### Using .env File

You can also configure previous keys in your **.env** file (recommended) using a comma-separated list:

```
encryption.key = hex2bin:your_new_key
encryption.previousKeys = hex2bin:old_key_1,hex2bin:old_key_2
```

The framework automatically parses the comma-separated string into an array and processes each key.

#### Key Rotation Workflow

1.  **Before rotation**: Data is encrypted and decrypted with `key`
2.  **Start rotation**: Move current `key` value to `previousKeys` array, set new value for `key`
3.  **During rotation**: New data encrypted with new `key`, old data still decryptable via `previousKeys`
4.  **Re-encrypt data** (optional): Decrypt and re-encrypt existing data with the new key
5.  **Complete rotation**: Once all data is re-encrypted, remove old keys from `previousKeys`

!!! important "Important"
    The `previousKeys` feature is for **decryption fallback only**. All new encryption operations always use the current `key`. If you pass an explicit key via the `$params` argument to `encrypt()` or `decrypt()`, the previousKeys fallback will not be used.

### Padding

Sometimes, the length of a message may provide a lot of information about its nature. If a message is one of "yes", "no" and "maybe", encrypting the message doesn't help: knowing the length is enough to know what the message is.

Padding is a technique to mitigate this, by making the length a multiple of a given block size.

Padding is implemented in `SodiumHandler` using libsodium's native `sodium_pad` and `sodium_unpad` functions. This requires the use of a padding length (in bytes) that is added to the plaintext message prior to encryption, and removed after decryption. Padding is configurable via the `$blockSize` property of `Config\Encryption`. This value should be greater than zero.

!!! important "Important"
    You are advised not to devise your own padding implementation. You must always use the more secure implementation of a library. Also, passwords should not be padded. Usage of padding in order to hide the length of a password is not recommended. A client willing to send a password to a server should hash it instead (even with a single iteration of the hash function). This ensures that the length of the transmitted data is constant, and that the server doesn't effortlessly get a copy of the password.

### Encryption Handler Notes

#### OpenSSL Notes

The [OpenSSL](https://www.php.net/openssl) extension has been a standard part of PHP for a long time.

CodeIgniter's OpenSSL handler uses the AES-256-CTR cipher.

The *key* your configuration provides is used to derive two other keys, one for encryption and one for authentication. This is achieved by way of a technique known as an [HMAC-based Key Derivation Function](https://en.wikipedia.org/wiki/HKDF) (HKDF).

#### Sodium Notes

The [Sodium](https://www.php.net/manual/en/book.sodium) extension is bundled by default in PHP as of PHP 7.2.0.

CodeIgniter's Sodium handler uses `sodium_crypto_secretbox()` for shared-key authenticated encryption. It uses XSalsa20 for encryption and Poly1305 for authentication. This handler does not perform public-key key exchange.

### Message Length

An encrypted string is usually longer than the original, plain-text string (depending on the cipher).

This is influenced by the cipher algorithm itself, the initialization vector (IV) prepended to the cipher-text, and the HMAC authentication message that is also prepended. Furthermore, the encrypted message is also Base64-encoded so that it is safe for storage and transmission regardless of the character set in use.

Keep this information in mind when selecting your data storage mechanism. Cookies, for example, can only hold 4K of information.

### Using the Encryption Service Directly

Instead of (or in addition to) using `Services` as described in [Using the Encryption Library](#using-the-encryption-library), you can create an "Encrypter" directly, or change the settings of an existing instance.

```php
<?php

// create an Encryption instance
$encryption = new \CodeIgniter\Encryption\Encryption();

// reconfigure an instance with different settings
$encrypter = $encryption->initialize($config);
```

Remember that `$config` must be an instance of the `Config\Encryption` class.

## Class Reference

## CodeIgniter\Encryption

### Encryption

#### createKey([$length = 32])

- **Parameters**  
    - **$length** `int` Output length

- **Returns**: A pseudo-random cryptographic key with the specified length, or `false` on failure

- **Return type**: `string`

Creates a cryptographic key by fetching random data from the operating system's sources (*i.e.* `/dev/urandom`).

#### initialize([Encryption $config = null])

- **Parameters**  
    - Config\Encryption `$config` Configuration parameters

- **Returns**: `CodeIgniter\Encryption\EncrypterInterface` instance

- **Return type**: `CodeIgniter\Encryption\EncrypterInterface`

- **Throws**: `CodeIgniter\Encryption\Exceptions\EncryptionException`

Initializes (configures) the library to use different settings.

Example:

```php
<?php

$encrypter = $encryption->initialize(['cipher' => 'AES-256-CTR']);
```

Please refer to the [Configuring the Library](#configuring-the-library) section for detailed info.

### CodeIgniter\Encryption\EncrypterInterface

#### encrypt($data[, $params = null])

- **Parameters**  
    - **$data** `string` Data to encrypt
    - **$params** `array|string|null` Configuration parameters (key)

- **Returns**: Encrypted data

- **Return type**: `string`

- **Throws**: `CodeIgniter\Encryption\Exceptions\EncryptionException`

Encrypts the input data and returns its ciphertext.

If you pass parameters as the second argument, the `key` element will be used as the starting key for this operation if `$params` is an array. Alternatively, the starting key may be passed as a string.

If you are using the SodiumHandler and want to pass a different `blockSize` at runtime, pass the `blockSize` key in the `$params` array.

Examples:

```php
<?php

$ciphertext = $encrypter->encrypt('My secret message');
$ciphertext = $encrypter->encrypt('My secret message', ['key' => 'New secret key']);
$ciphertext = $encrypter->encrypt('My secret message', ['key' => 'New secret key', 'blockSize' => 32]);
$ciphertext = $encrypter->encrypt('My secret message', 'New secret key');
$ciphertext = $encrypter->encrypt('My secret message', ['blockSize' => 32]);
```

#### decrypt($data[, $params = null])

- **Parameters**  
    - **$data** `string` Data to decrypt
    - **$params** `array|string|null` Configuration parameters (key)

- **Returns**: Decrypted data

- **Return type**: `string`

- **Throws**: `CodeIgniter\Encryption\Exceptions\EncryptionException`

Decrypts the input data and returns it in plain text.

If you pass parameters as the second argument, the `key` element will be used as the starting key for this operation if `$params` is an array. Alternatively, the starting key may be passed as a string.

If you are using the SodiumHandler and want to pass a different `blockSize` at runtime, pass the `blockSize` key in the `$params` array.

Examples:

```php
<?php

echo $encrypter->decrypt($ciphertext);
echo $encrypter->decrypt($ciphertext, ['key' => 'New secret key']);
echo $encrypter->decrypt($ciphertext, ['key' => 'New secret key', 'blockSize' => 32]);
echo $encrypter->decrypt($ciphertext, 'New secret key');
echo $encrypter->decrypt($ciphertext, ['blockSize' => 32]);
```
