#########################
Upgrading from 3.x to 4.x
#########################

CodeIgniter 4 is a rewrite of the framework and is not backwards compatible.
It is more appropriate to think of converting your app, rather than upgrading it.
Once you have done that, upgrading from one version of CodeIgniter 4 to the next
will be straightforward.

The "lean, mean and simple" philosophy has been retained, but the
implementation has a lot of differences, compared to CodeIgniter 3.

There is no 12-step checklist for upgrading. Instead, start with a copy
of CodeIgniter 4 in a new project folder,
:doc:`however you wish to install and use it </installation/index>`,
and then convert and integrate your app components.
We'll try to point out the most important considerations here.

To upgrade your project, we figured out two major tasks you have to work on.
First of all, there are some general adjustments which are significant to every
project and have to be handled. The second one are the libraries in which
CodeIgniter is built up and contain some of the most important functions.
These libraries operate separately from each other, so you have to look at
them one by one.

**Do read the user guide** before embarking on a project conversion!

.. contents::
    :local:
    :depth: 2

General Adjustments
*******************

Downloads
=========

- CI4 is still available as a ready-to-run zip or tarball.
- It can also be installed using Composer.

Namespaces
==========

- CI4 is built for PHP 7.4+, and everything in the framework is namespaced, except for the helpers.

Application Structure
=====================

- The **application** folder is renamed as **app** and the framework still has **system** folders,
  with the same interpretation as before.
- The framework now provides for a **public** folder, intended as the document root for your app.
- There is also a **writable** folder, to hold cache data, logs, and session data.
- The **app** folder looks very similar to **application** for CI3, with some
  name changes, and some subfolders moved to the **writable** folder.
- There is no longer a nested **application/core** folder, as we have
  a different mechanism for extending framework components (see below).

Routing
=======

- The Auto Routing is disabled by default. If you want to use the Auto Routing in the same way as CI3, you need to enable :ref:`auto-routing`.
- CI4 also has an optional new more secure :ref:`auto-routing-improved`.

Model, View and Controller
==========================

- CodeIgniter is based on the MVC concept. Thus, the changes on the Model, View and Controller
  are one of the most important things you have to handle.
- In CodeIgniter 4, models are now located in **app/Models** and you have to add the lines
  ``namespace App\Models;`` along with ``use CodeIgniter\Model;`` right after the opening php tag.
  The last step is to replace ``extends CI_Model`` with ``extends Model``.
- The views of CodeIgniter 4 have been moved to **app/Views**. Furthermore, you have to change
  the syntax of loading views from ``$this->load->view('directory_name/file_name')`` to
  ``echo view('directory_name/file_name');``.
- Controllers of CodeIgniter 4 have to be moved to **app/Controllers**. After that,
  add ``namespace App\Controllers;`` after the opening php tag.
  Lastly, replace ``extends CI_Controller`` with ``extends BaseController``.
- For more information we recommend you the following upgrade guides, which will give
  you some step-by-step instructions to convert the MVC classes in CodeIgniter4:

.. toctree::
    :titlesonly:

    upgrade_models
    upgrade_views
    upgrade_controllers

Class Loading
=============

- There is no longer a CodeIgniter "superobject", with framework component
  references magically injected as properties of your controller.
- Classes are instantiated where needed, and components are managed
  by ``Services``.
- The class loader automatically handles PSR-4 style class locating,
  within the ``App`` (**app**) and ``CodeIgniter`` (i.e., **system**) top level
  namespaces; with Composer autoloading support.
- You can configure the class loading to support whatever application structure
  you are most comfortable with, including the "HMVC" style.

Libraries
=========

- Your app classes can still go inside **app/Libraries**, but they don't have to.
- Instead of CI3's ``$this->load->library(x);`` you can now use
  ``$this->x = new X();``, following namespaced conventions for your component.

Helpers
=======

- Helpers are pretty much the same as before, though some have been simplified.
- In CI4, ``redirect()`` returns a ``RedirectResponse`` instance instead of redirecting and terminating script execution. You must return it.
    - `redirect() Documentation CodeIgniter 3.X <https://codeigniter.com/userguide3/helpers/url_helper.html#redirect>`_
    - `redirect() Documentation CodeIgniter 4.X <../general/common_functions.html#redirect>`_

Events
======

- Hooks have been replaced by Events.
- Instead of CI3's ``$hook['post_controller_constructor']`` you now use
  ``Events::on('post_controller_constructor', ['MyClass', 'MyFunction']);``, with the namespace ``CodeIgniter\Events\Events;``.
- Events are always enabled, and are available globally.

Extending the Framework
=======================

- You don't need a **core** folder to hold ``MY_...`` framework
  component extensions or replacements.
- You don't need ``MY_x`` classes inside your libraries folder
  to extend or replace CI4 pieces.
- Make any such classes where you like, and add appropriate
  service methods in **app/Config/Services.php** to load
  your components instead of the default ones.

Upgrading Libraries
*******************

- Your app classes can still go inside **app/Libraries**, but they don't have to.
- Instead of CI3's ``$this->load->library(x);`` you can now use ``$this->x = new X();``,
  following namespaced conventions for your component.
- Some libraries from CodeIgniter 3 no longer exists in Version 4. For all these
  libraries, you have to find a new way to implement your functions. These
  libraries are `Calendaring <http://codeigniter.com/userguide3/libraries/calendar.html>`_,
  `FTP <http://codeigniter.com/userguide3/libraries/ftp.html>`_,
  `Javascript <http://codeigniter.com/userguide3/libraries/javascript.html>`_,
  `Shopping Cart <http://codeigniter.com/userguide3/libraries/cart.html>`_,
  `Trackback <http://codeigniter.com/userguide3/libraries/trackback.html>`_,
  `XML-RPC /-Server <http://codeigniter.com/userguide3/libraries/xmlrpc.html>`_,
  and `Zip Encoding <http://codeigniter.com/userguide3/libraries/zip.html>`_.
- CI3's `Input <http://codeigniter.com/userguide3/libraries/input.html>`_ corresponds to CI4's :doc:`IncomingRequest </incoming/incomingrequest>`.
- CI3's `Output <http://codeigniter.com/userguide3/libraries/output.html>`_ corresponds to CI4's :doc:`Responses </outgoing/response>`.
- All the other libraries, which exist in both CodeIgniter versions, can be upgraded with some adjustments.
  The most important and mostly used libraries received an Upgrade Guide, which will help you with simple
  steps and examples to adjust your code.

.. toctree::
    :titlesonly:

    upgrade_configuration
    upgrade_database
    upgrade_emails
    upgrade_encryption
    upgrade_file_upload
    upgrade_html_tables
    upgrade_localization
    upgrade_migrations
    upgrade_pagination
    upgrade_responses
    upgrade_routing
    upgrade_security
    upgrade_sessions
    upgrade_validations
    upgrade_view_parser

.. note::
    More upgrade guides coming soon
