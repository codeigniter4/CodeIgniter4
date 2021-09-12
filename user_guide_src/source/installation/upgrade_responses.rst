Upgrade HTTP Responses
######################

.. contents::
    :local:
    :depth: 1


Documentations
==============
- `Output Class Documentation Codeigniter 3.X <http://codeigniter.com/userguide3/libraries/output.html>`_
- :doc:`HTTP Responses Documentation Codeigniter 4.X </outgoing/response>`

What has been changed
=====================
- The methods have been renamed

Upgrade Guide
=============
1. The methods in the HTTP Responses class are named slightly different. The most important change in the naming is the switch from underscored method names to camelCase. The method ``set_content_type()`` from version 3 is now named ``setContentType()`` and so on.
2. In the most cases you have to change ``$this->output`` to ``$this->response`` followed by the method. You can find all methods :doc:`here </outgoing/response>`.

Code Example
============

Codeigniter Version 3.11
------------------------
::

    $this->output->set_status_header(404);

    ...

    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode(array('foo' => 'bar')));

Codeigniter Version 4.x
-----------------------
::

    $this->response->setStatusCode(404);

    ...

    return $this->response->setJSON(['foo' => 'bar']);
