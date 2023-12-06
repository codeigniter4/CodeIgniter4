################
Helper Functions
################

.. contents::
    :local:
    :depth: 2

*****************
What are Helpers?
*****************

Helpers, as the name suggests, help you with tasks. Each helper file is simply a
collection of functions in a particular category. There are
:doc:`URL Helpers <../helpers/url_helper>`,
that assist in creating links, there are :doc:`Form Helpers <../helpers/form_helper>`
that help you create form elements, :doc:`Test Helpers <../helpers/text_helper>`
perform various text formatting routines,  :doc:`Cookie Helpers <../helpers/cookie_helper>`
set and read cookies, :doc:`Filesystem Helpers <../helpers/filesystem_helper>` help you
deal with files, etc.

Unlike most other systems in CodeIgniter, Helpers are not written in an
Object Oriented format. They are simple, procedural functions. Each
helper function performs one specific task, with no dependence on other
functions.

CodeIgniter does not load Helper Files by default, so the first step in
using a Helper is to load it. Once loaded, it becomes globally available
in your :doc:`controller <../incoming/controllers>` and
:doc:`views <../outgoing/views>`.

Helpers are typically stored in your **system/Helpers**, or
**app/Helpers** directory.

***************
Loading Helpers
***************

.. note:: The :doc:`../helpers/url_helper` is always loaded so you do not need to load it yourself.

Loading a Helper
================

Loading a helper file is quite simple using the following method:

.. literalinclude:: helpers/001.php

The above code loads the **name_helper.php** file.

.. important:: CodeIgniter helper file names are all lowercase.
    Therefore, ``helper('Name')`` will not work on case-sensitive file systems
    such as Linux.

For example, to load the :doc:`../helpers/cookie_helper` file, which is named
**cookie_helper.php**, you would do this:

.. literalinclude:: helpers/002.php

.. note:: The :php:func:`helper()` function does not return a value, so
    don't try to assign it to a variable. Just use it as shown.

Auto-Discovery and Composer Packages
------------------------------------

By default, CodeIgniter will search for the helper files in all defined namespaces
by :ref:`auto-discovery`.
You can check your defined namespaces by the spark command. See :ref:`confirming-namespaces`.

If you use many Composer packages, you will have many defined namespaces.
CodeIgniter will scan all namespaces by default.

To avoid wasting time scanning for irrelevant Composer packages, you can manually
specify packages for Auto-Discovery. See :ref:`modules-specify-composer-packages`
for details.

Or you can :ref:`specify a namespace <helpers-loading-from-specified-namespace>`
for a helper that you want to load.

Load Order
----------

The :php:func:`helper()` function will scan through all defined namespaces and
load in ALL matching helpers of the same name. This allows any module's helpers
to be loaded, as well as any helpers you've created specifically for this application.

The load order is as follows:

1. app/Helpers - Files loaded here are always loaded first.
2. {namespace}/Helpers - All namespaces are looped through in the order they are defined.
3. system/Helpers - The base file is loaded last.

Loading Multiple Helpers
========================

If you need to load more than one helper at a time, you can pass
an array of file names in and all of them will be loaded:

.. literalinclude:: helpers/003.php

Loading in a Controller
=======================

A helper can be loaded anywhere within your controller methods (or
even within your View files, although that's not a good practice), as
long as you load it before you use it.

You can load your helpers in your
controller constructor so that they become available automatically in
any method, or you can load a helper in a specific method that needs
it.

However if you want to load in your controller constructor, you can use the ``$helpers``
property in Controller instead. See :ref:`Controllers <controllers-helpers>`.

.. _helpers-loading-from-specified-namespace:

Loading from Specified Namespace
================================

By default, CodeIgniter will search for the helper files in all defined namespaces
and load all found files.

If you want to load only a helper in a specific namespace, prefix the name of the
helper with the namespace that it can be located in. Within that namespaced directory,
the loader expects it to live within a sub-directory named **Helpers**. An example
will help understand this.

For this example, assume that we have grouped together all of our Blog-related
code into its own namespace, ``Example\Blog``. The files exist on our server at
**Modules/Blog/**. So, we would put our Helper files for the blog module in
**Modules/Blog/Helpers/**. A **blog_helper** file would be at
**Modules/Blog/Helpers/blog_helper.php**. Within our controller we could
use the following command to load the helper for us:

.. literalinclude:: helpers/004.php

You can also use the following way:

.. literalinclude:: helpers/007.php

.. note:: The functions within files loaded this way are not truly namespaced.
    The namespace is simply used as a convenient way to locate the files.

.. _auto-loading-helpers:

Auto-loading Helpers
====================

.. versionadded:: 4.3.0

If you find that you need a particular helper globally throughout your application,
you can tell CodeIgniter to auto-load it during system initialization.
This is done by opening the **app/Config/Autoload.php** file
and adding the helper to the ``$helpers`` property.

**************
Using a Helper
**************

Once you've loaded the Helper File containing the function you intend to
use, you'll call it the way you would a standard PHP function.

For example, to create a link using the :php:func:`anchor()` function in one of
your view files you would do this:

.. literalinclude:: helpers/005.php

Where ``Click Here`` is the name of the link, and ``blog/comments`` is the
URI to the controller/method you wish to link to.

****************
Creating Helpers
****************

Creating Custom Helpers
=======================

The helper filename is the **helper name** and **_helper.php**.

For example, to create info helper, you’ll create a file named
**app/Helpers/info_helper.php**, and add a function to the file:

.. literalinclude:: helpers/008.php

You can add as many functions as you like to a single helper file.

"Extending" Helpers
===================

To "extend" Helpers, create a file in your **app/Helpers** folder
with an identical name to the existing Helper.

If all you need to do is add some functionality to an existing helper -
perhaps add a function or two, or change how a particular helper
function operates - then it's overkill to replace the entire helper with
your version. In this case, it's better to simply "extend" the Helper.

.. note:: The term "extend" is used loosely since Helper functions are
    procedural and discrete and cannot be extended in the traditional
    programmatic sense. Under the hood, this gives you the ability to
    add to, or to replace the functions a Helper provides.

For example, to extend the native **Array Helper** you'll create a file
named **app/Helpers/array_helper.php**, and add or override
functions:

.. literalinclude:: helpers/006.php

.. important:: Do not specify the namespace ``App\Helpers``.

See `Load Order`_ for the order in which helper files are loaded.

*********
Now What?
*********

In the Table of Contents, you'll find a list of all the available :doc:`Helpers <../helpers/index>`.
Browse each one to see what they do.
