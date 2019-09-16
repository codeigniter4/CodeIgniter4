############
View Layouts
############

.. contents::
    :local:
    :depth: 2

CodeIgniter supports a simple, yet very flexible, layout system that makes it simple to use one or more
base page layouts across your application. Layouts support sections of content that can be inserted from
any view being rendered. You could create different layouts to support one-column, two-column,
blog archive pages, and more. Layouts are never directly rendered. Instead, you render a view, which
specifies the layout that it wants to extend.

*****************
Creating A Layout
*****************

Layouts are views like any other. The only difference is their intended usage. Layouts are the only view
files that would make use of the ``renderSection()`` method. This method acts as a placeholder for content.

::

    <!doctype html>
    <html>
    <head>
        <title>My Layout</title>
    </head>
    <body>
        <?= $this->renderSection('content') ?>
    </body>
    </html>

The renderSection() method only has one argument - the name of the section. That way any child views know
what to name the content section.

**********************
Using Layouts in Views
**********************

Whenever a view wants to be inserted into a layout, it must use the ``extend()`` method at the top of the file::

    <?= $this->extend('default') ?>

The extend method takes the name of any view file that you wish to use. Since they are standard views, they will
be located just like a view. By default, it will look in the application's View directory, but will also scan
other PSR-4 defined namespaces. You can include a namespace to locate the view in particular namespace View directory::

    <?= $this->extend('Blog\Views\default') ?>

All content within a view that extends a layout must be included within ``section($name)`` and ``endSection()`` method calls.
Any content between these calls will be inserted into the layout wherever the ``renderSection($name)`` call that
matches the section name exists.::

    <?= $this->extend('default') ?>

    <?= $this->section('content') ?>
        <h1>Hello World!</h1>
    <?= $this->endSection() ?>

The ``endSection()`` does not need the section name. It automatically knows which one to close.

******************
Rendering the View
******************

Rendering the view and it's layout is done exactly as any other view would be displayed within a controller::

    public function index()
    {
        echo view('some_view');
    }

The renderer is smart enough to detect whether the view should be rendered on its own, or if it needs a layout.

***********************
Including View Partials
***********************

View partials are view files that do not extend any layout. They typically include content that can be reused from
view to view. When using view layouts you must use ``$this->include()`` to include any view partials.

::

    <?= $this->extend('default') ?>

    <?= $this->section('content') ?>
        <h1>Hello World!</h1>

        <?= $this->include('sidebar') ?>
    <?= $this->endSection() ?>

When calling the include() method, you can pass it all of the same options that can when rendering a normal view, including
cache directives, etc.
