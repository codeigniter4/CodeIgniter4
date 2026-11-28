# HTTP Method Spoofing

When working with HTML forms, you can only use GET or POST HTTP verbs. In most cases, this is just fine. However, to support RESTful routing, you need to support other, more correct, verbs, like DELETE or PUT. Since browsers don't support this, CodeIgniter provides you with a way to spoof the method that is being used. This allows you to make a POST request, but tell the application that it should be treated as a different request type.

To spoof the method, a hidden input is added to the form with the name of `_method`. Its value is the HTTP verb that you want the request to be:

```
<form action="" method="post">
    <input type="hidden" name="_method" value="PUT">
</form>
```

This form is converted into a PUT request and is a true PUT request as far as routing, the IncomingRequest class, and [method-specific filters](filters.md#methods) are concerned. For example, a POST form with `_method=PUT` runs filters configured for `PUT`, not filters configured for `POST`. If you configure CSRF or other security filters by method, include every method you need to protect.

The form that you are using must be a POST request. GET requests cannot be spoofed.

!!! note "Note"
    Be sure to check your web server's configuration as some servers do not support all HTTP verbs with the default configuration, and must have additional packages enabled to work.
