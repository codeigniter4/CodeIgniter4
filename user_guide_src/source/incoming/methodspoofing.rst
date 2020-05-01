====================
HTTP Method Spoofing
====================

When working with HTML forms you can only use GET or POST HTTP verbs. In most cases, this is just fine. However, to
support REST-ful routing you need to support other, more correct, verbs, like DELETE or PUT. Since the browsers
don't support this, CodeIgniter provides you with a way to spoof the method that is being used. This allows you to
make a POST request, but tell the application that it should be treated as a different request type.

To spoof the method, a hidden input is added to the form with the name of ``_method``. It's value is the HTTP verb
that you want the request to be::

    <form action="" method="post">
        <input type="hidden" name="_method" value="PUT" />
    </form>

This form is converted into a PUT request and is a true PUT request as far as the routing and the IncomingRequest
class are concerned.

The form that you are using must be a POST request. GET requests cannot be spoofed.

.. note:: Be sure to check your web server's configuration as some servers do not support all HTTP verbs
    with the default configuration, and must have additional packages enabled to work.
