Upgrade Encryption
##################

.. contents::
    :local:
    :depth: 2

Documentations
**************

- `Encryption Library Documentation CodeIgniter 3.X <http://codeigniter.com/userguide3/libraries/encryption.html>`_
- :doc:`Encryption Service Documentation CodeIgniter 4.X </libraries/encryption>`

What has been changed
*********************

- The support for ``MCrypt`` has been dropped, as that has been deprecated as of PHP 7.2.

Upgrade Guide
*************

1. Within your configs the ``$config['encryption_key'] = 'abc123';`` moved from **application/config/config.php** to ``public $key = 'abc123';`` in **app/Config/Encryption.php**.
2. If you need to decrypt data encrypted with CI3's Encryption, configure settings to maintain compatibility. See :ref:`encryption-compatible-with-ci3`.
3. Wherever you have used the encryption library you have to replace ``$this->load->library('encryption');`` with ``$encrypter = service('encrypter');`` and change the methods for encryption and decrypting like in the following code example.

Code Example
************

CodeIgniter Version 3.x
=======================

.. literalinclude:: upgrade_encryption/ci3sample/001.php

CodeIgniter Version 4.x
=======================

.. literalinclude:: upgrade_encryption/001.php
