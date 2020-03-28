################
CodeIgniter URLs
################

By default, URLs in CodeIgniter are designed to be search-engine and human-friendly. Rather than using the standard
"query-string" approach to URLs that is synonymous with dynamic systems, CodeIgniter uses a **segment-based** approach::

	example.com/news/article/my_article

URI Segments
============

The segments in the URL, in following with the Model-View-Controller approach, usually represent::

	example.com/class/method/ID

1. The first segment represents the controller **class** that should be invoked.
2. The second segment represents the class **method** that should be called.
3. The third, and any additional segments, represent the ID and any variables that will be passed to the controller.

The :doc:`URI Library <../libraries/uri>` and the :doc:`URL Helper <../helpers/url_helper>` contain functions that make it easy
to work with your URI data. In addition, your URLs can be remapped using the :doc:`URI Routing </incoming/routing>`
feature for more flexibility.

Removing the index.php file
===========================

By default, the **index.php** file will be included in your URLs::

	example.com/index.php/news/article/my_article

If your server supports rewriting URLs you can easily remove this file with URL rewriting. This is handled differently
by different servers, but we will show examples for the two most common web servers here.

Apache Web Server
-----------------

Apache must have the *mod_rewrite* extension enabled. If it does, you can use a ``.htaccess`` file with some simple rules.
Here is an example of such a file, using the "negative" method in which everything is redirected except the specified
items:

.. code-block:: apache

	RewriteEngine On
	RewriteCond %{REQUEST_FILENAME} !-f
	RewriteCond %{REQUEST_FILENAME} !-d
	RewriteRule ^(.*)$ index.php/$1 [L]

In this example, any HTTP request other than those for existing directories and existing files are treated as a
request for your index.php file.

.. note:: These specific rules might not work for all server configurations.

.. note:: Make sure to also exclude from the above rules any assets that you might need to be accessible from the outside world.

NGINX
-----

Under NGINX, you can define a location block and use the ``try_files`` directive to get the same effect as we did with
the above Apache configuration:

.. code-block:: nginx

	location / {
		try_files $uri $uri/ /index.php$is_args$args;
	}

This will first look for a file or directory matching the URI (constructing the full path to each file from the
settings of the root and alias directives), and then sends the request to the index.php file along with any arguments.
