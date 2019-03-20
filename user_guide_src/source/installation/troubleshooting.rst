###############
Troubleshooting
###############

Here are some common installation problems, and suggested workarounds.

How do I know if my install is working?
------------------------------------------------------------------------

From the command line, at your project root::

    php spark serve

``http://localhost:8080`` in your browser should then show the default
welcome page:

|CodeIgniter4 Welcome|

I have to include index.php in my URL
-------------------------------------

If a URL like ``/mypage/find/apple`` doesn't work, but the similar
URL ``/index.php/mypage/find/apple`` does, that sounds like your ``.htaccess`` rules
(for Apache) are not setup properly, or the ``mod_rewrite`` extension
in Apache's ``httpd.conf`` is commented out.

Only the default page loads
---------------------------

If you find that no matter what you put in your URL only your default
page is loading, it might be that your server does not support the
REQUEST_URI variable needed to serve search-engine friendly URLs. As a
first step, open your *app/Config/App.php* file and look for
the URI Protocol information. It will recommend that you try a couple of
alternate settings. If it still doesn't work after you've tried this
you'll need to force CodeIgniter to add a question mark to your URLs. To
do this open your *app/Config/App.php* file and change this::

	public $indexPage = 'index.php';

To this::

	public $indexPage = 'index.php?';

The tutorial gives 404 errors everywhere :(
-------------------------------------------

You can't follow the tutorial using PHP's built-in web server.
It doesn't process the `.htaccess` file needed to route
requests properly.

The solution: use Apache to serve your site, or else the built-in
CodeIgniter equivalent, ``php spark serve`` from your project root.

.. |CodeIgniter4 Welcome| image:: ../images/welcome.png