Upgrade Encryption
##################

.. contents::
    :local:
    :depth: 1


Documentations
==============

- `Encryption Library Documentation Codeigniter 3.X <http://codeigniter.com/userguide3/libraries/encryption.html>`_
- :doc:`Encryption Service Documentation Codeigniter 4.X </libraries/encryption>`


What has been changed
=====================
- The support for ``MCrypt`` has been dropped, as that has been deprecated as of PHP 7.2.

Upgrade Guide
=============
1. Within your configs the ``$config['encryption_key'] = 'abc123';`` moved from ``application/config/config.php`` to ``public $key = 'abc123';`` in ``app/Config/Encryption.php``.
2. Wherever you have used the encryption library you have to replace ``$this->load->library('encryption');`` with ``$encrypter = service('encrypter');`` and change the methods for encryption and decrypting like in the following code example.

Code Example
============

Codeigniter Version 3.11
------------------------
::

    $this->load->library('encryption');

    $plain_text = 'This is a plain-text message!';
    $ciphertext = $this->encryption->encrypt($plain_text);

    // Outputs: This is a plain-text message!
    echo $this->encryption->decrypt($ciphertext);


Codeigniter Version 4.x
-----------------------
::

    $encrypter = service('encrypter');

    $plainText = 'This is a plain-text message!';
    $ciphertext = $encrypter->encrypt($plainText);

    // Outputs: This is a plain-text message!
    echo $encrypter->decrypt($ciphertext);
