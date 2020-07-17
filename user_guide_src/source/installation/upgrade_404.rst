#############################
Upgrading from 4.0.x to 4.0.4
#############################

CodeIgniter 4.0.4 fixes a bug in the implementation of :doc:`Controller Filters </incoming/filters>`, breaking
code implementing the ``FilterInterface``.

**Update FilterInterface declarations**

The method signatures for ``after()`` and ``before()`` must be updated to include ``$arguments``. The function
definitions should be changed from::

    public function before(RequestInterface $request)
    public function after(RequestInterface $request, ResponseInterface $response)

to::

    public function before(RequestInterface $request, $arguments = null)
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)

