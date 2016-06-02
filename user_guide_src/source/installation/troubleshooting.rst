###############
Troubleshooting
###############

Here are some common installation problems, and suggested workarounds.

I have to include index.php in my URL
-------------------------------------

If a URL like ``/mypage/find/apple`` doesn't work, but the similar
URL ``/index.php/mypage/find/apple`` does, then your ``.htaccess`` rules
(for Apache) are not setup properly.

Only the default page loads
---------------------------

If you find that no matter what you put in your URL only your default
page is loading, it might be that your server does not support the
REQUEST_URI variable needed to serve search-engine friendly URLs. As a
first step, open your *application/Config/App.php* file and look for
the URI Protocol information. It will recommend that you try a couple of
alternate settings. If it still doesn't work after you've tried this
you'll need to force CodeIgniter to add a question mark to your URLs. To
do this open your *application/Config/App.php* file and change this::

	public $indexPage = 'index.php';

To this::

	public $indexPage = 'index.php?';
