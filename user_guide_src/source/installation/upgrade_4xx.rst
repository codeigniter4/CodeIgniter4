#############################
Upgrading from 3.x to 4.x
#############################

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

Not all of the CI3 libraries have been ported or rewritten for CI4!
See the threads in the `CodeIgniter 4 Roadmap <https://forum.codeigniter.com/forum-33.html>`_
subforum for an up-to-date list!

**Do read the user guide** before embarking on a project conversion!

**Downloads**

- CI4 is still available as a ready-to-run zip or tarball, which
  includes the user guide (though in the `docs` subfolder
- It can also be installed using Composer

**Namespaces**

- CI4 is built for PHP7.2+, and everything in the framework is namespaced, except for the helpers.

**Application Structure**

- The framework still has ``app`` and ``system`` folders, with the same
  interpretation as before
- The framework now provides for a ``public`` folder, intended as the document
  root for your app
- There is also a ``writable`` folder, to hold cache data, logs, and session data
- The ``application`` folder looks very similar to that for CI3, with some
  name changes, and some subfolders
  moved to the ``writable`` folder
- There is no longer a nested ``application/core`` folder, as we have
  a different mechanism for extending framework components (see below)

**Class loading**

- There is no longer a CodeIgniter "superobject", with framework component
  references magically injected as properties of your controller
- Classes are instantiated where needed, and components are managed
  by ``Services``
- The class loader automatically handles PSR4 style class locating,
  within the ``App`` (application) and ``CodeIgniter`` (i.e. system) top level
  namespaces; with composer autoloading support, and even using educated
  guessing to find your models and libraries if they are in the right
  folder even though not namespaced
- You can configure the class loading to support whatever application structure
  you are most comfortable with, including the "HMVC" style

**Controllers**

- Controllers extend \\CodeIgniter\\Controller instead of CI_Controller
- They don't use a constructor any more (to invoke CI "magic") unless
  that is part of a base controller you make
- CI provides ``Request`` and ``Response`` objects for you to work with -
  more powerful than the CI3-way
- If you want a base controller (MY_Controller in CI3), make it
  where you like, e.g. BaseController extends Controller, and then
  have your controllers extend it

**Models**

- Models extend \\CodeIgniter\\Model instead of CI_Model
- The CI4 model has much more functionality, including automatic
  database connection, basic CRUD, in-model validation, and
  automatic pagination
- CI4 also has the ``Entity`` class you can build on, for
  richer data mapping to your database tables
- Instead of CI3's ``$this->load->model(x);``, you would now use
  ``$this->x = new X();``, following namespaced conventions for your component

**Views**

- Your views look much like before, but they are invoked differently ...
  instead of CI3's ``$this->load->view(x);`` you can use ``echo view(x);``
- CI4 supports view "cells", to build your response in pieces
- The template parser is still there, but substantially
  enhanced

**Libraries**

- Your app classes can still go inside ``app/Libraries``, but they
  don't have to
- Instead of CI3's ``$this->load->library(x);`` you can now use
  ``$this->x = new X();``, following namespaced conventions for your
  component

**Helpers**

- Helpers are pretty much the same as before, though some have been simplified

**Extending the framework**

- You don't need a ``core`` folder to hold ``MY_...`` framework
  component extensions or replacements
- You don't need ``MY_x`` classes inside your libraries folder
  to extend or replace CI4 pieces
- Make any such classes where you like, and add appropriate
  service methods in ``app/Config/Services.php`` to load
  your components instead of the default ones
