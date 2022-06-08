###############
View Decorators
###############

View Decorators allow your application to modify the HTML output during the rendering process. This happens just
prior to being cached, and allows you to apply custom functionality to your views.

*******************
Creating Decorators
*******************

Creating your own view decorators requires creating a new class that implements ``CodeIgniter\View\ViewDecoratorInterface``.
This requires a single method that takes the generated HTML string, performs any modifications on it, and returns
the resulting HTML.

.. literalinclude:: view_decorators/001.php

Once created, the class must be registered in ``app/Config/View.php``:

.. literalinclude:: view_decorators/002.php

Now that it's registered the decorator will be called for every view that is rendered or parsed.
Decorators are called in the order specified in this configuration setting.
