##############
AJAX Requests
##############

The ``IncomingRequest::isAJAX()`` method uses the ``X-Requested-With`` header to define whether the request is XHR or normal. However, the most recent JavaScript implementations (i.e. fetch) no longer send this header along with the request, thus the use of ``IncomingRequest::isAJAX()`` becomes less reliable, because without this header it is not possible to define whether the request is or not XHR.

To get around this problem, the most efficient solution (so far) is to manually define the request header, forcing the information to be sent to the server, which will then be able to identify that the request is XHR.

Here's how to force the ``X-Requested-With`` header to be sent in the Fetch API and other JavaScript libraries.

Fetch API
=========

    fetch(url, {
        method: "get",
        headers: {

          "Content-Type": "application/json",

          "X-Requested-With": "XMLHttpRequest"

        }

    });


jQuery
======

For libraries like jQuery for example, it is not necessary to make explicit the sending of this header, because according to the official documentation <https://api.jquery.com/jquery.ajax/> it is a standard header for all requests ``$.ajax()``. But if you still want to force the shipment to not take risks, just do it as follows:

    $.ajax({
        url: "your url",

        headers: {'X-Requested-With': 'XMLHttpRequest'}

    });  


VueJS
=====

In VueJS you just need to add the following code to the ``created`` function, as long as you are using Axios for this type of request.

    axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';


React
=====

    axios.get("your url", {headers: {'Content-Type': 'application/json'}})