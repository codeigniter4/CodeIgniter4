************************
Extending the Controller
************************

CodeIgniter's core Controller should not be changed, but a default class extension is provided for you at
**app/Controllers/BaseController.php**. Any new controllers you make should extend ``BaseController`` to take
advantage of preloaded components and any additional functionality you provide:

.. literalinclude:: basecontroller/001.php

.. contents::
    :local:
    :depth: 2

Preloading Components
=====================

The base controller is a great place to load any helpers, models, libraries, services, etc. that you intend to
use every time your project runs. Helpers should be added to the pre-defined ``$helpers`` array. For example, if
you want the HTML and Text helpers universally available:

.. literalinclude:: basecontroller/002.php

Any other components to load or data to process should be added to the constructor ``initController()``. For
example, if your project uses the Session Library heavily you may want to initiate it here:

.. literalinclude:: basecontroller/003.php

Additional Methods
==================

The base controller is not routable. As an added security
measure **all** new methods you create should be declared as ``protected`` or ``private`` and only be accessed through the
controllers you create that extend ``BaseController``.

Other Options
=============

You may find that you need more than one base controller. You can create new base controllers as long as any other controllers that you make extend the correct base. For example, if your project
has an involved public interface and a simple administrative portal you may want to extend ``BaseController`` to
the public controllers and make ``AdminController`` for any administrative controllers.

If you do not want to use the base controller you may bypass it by having your controllers extend the system
Controller instead:

.. literalinclude:: basecontroller/004.php
