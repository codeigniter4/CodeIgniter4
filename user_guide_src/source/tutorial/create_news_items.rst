Create News Items
#################

.. contents::
    :local:
    :depth: 3

You now know how you can read data from a database using CodeIgniter, but
you haven't written any information to the database yet. In this section,
you'll expand your news controller and model created earlier to include
this functionality.

Enable CSRF Filter
******************

Before creating a form, let's enable the CSRF protection.

Open the **app/Config/Filters.php** file and update the ``$methods`` property like the following:

.. literalinclude:: create_news_items/001.php

It configures the CSRF filter to be enabled for all **POST** requests.
You can read more about the CSRF protection in :doc:`Security <../libraries/security>` library.

.. Warning:: In general, if you use ``$methods`` filters, you should :ref:`disable Auto Routing (Legacy) <use-defined-routes-only>`
    because :ref:`auto-routing-legacy` permits any HTTP method to access a controller.
    Accessing the controller with a method you don't expect could bypass the filter.

Adding Routing Rules
********************

Before you can start adding news items into your CodeIgniter application
you have to add an extra rule to **app/Config/Routes.php** file. Make sure your
file contains the following:

.. literalinclude:: create_news_items/004.php

The route directive for ``'news/new'`` is placed before the directive for ``'news/(:segment)'`` to ensure that the form to create a news item is displayed.

The ``$routes->post()`` line defines the router for a POST request. It matches
only a POST request to the URI path **/news**, and it maps to the ``create()`` method of
the ``News`` class.

You can read more about different routing types in :ref:`defined-route-routing`.

Create a Form
*************

Create news/create View File
============================

To input data into the database, you need to create a form where you can
input the information to be stored. This means you'll be needing a form
with two fields, one for the title and one for the text. You'll derive
the slug from our title in the model.

Create a new view at **app/Views/news/create.php**:

.. literalinclude:: create_news_items/006.php

There are probably only four things here that look unfamiliar.

The :php:func:`session()` function is used to get the Session object,
and ``session()->getFlashdata('error')`` is used to display the error related to CSRF protection
to the user. However, by default, if a CSRF validation check fails, an exception will be thrown,
so it does not work yet. See :ref:`csrf-redirection-on-failure` for more information.

The :php:func:`validation_list_errors()` function provided by the :doc:`../helpers/form_helper`
is used to report errors related to form validation.

The :php:func:`csrf_field()` function creates a hidden input with a CSRF token that helps protect against some common attacks.

The :php:func:`set_value()` function provided by the :doc:`../helpers/form_helper` is used to show
old input data when errors occur.

News Controller
===============

Go back to your ``News`` controller.

Add News::new() to Display the Form
-----------------------------------

First, create a method to display the HTML form you have created.

.. literalinclude:: create_news_items/002.php

We load the :doc:`Form helper <../helpers/form_helper>` with the
:php:func:`helper()` function. Most helper functions require the helper to be
loaded before use.

Then it returns the created form view.

Add News::create() to Create a News Item
----------------------------------------

Next, create a method to create a news item from the submitted data.

You're going to do three things here:

1. checks whether the submitted data passed the validation rules.
2. saves the news item to the database.
3. returns a success page.

.. literalinclude:: create_news_items/005.php

The code above adds a lot of functionality.

Validate the Data
^^^^^^^^^^^^^^^^^

You'll use the Controller-provided helper function :ref:`validate() <controller-validate>` to validate the submitted data.
In this case, the title and body fields are required and in the specific length.
CodeIgniter has a powerful validation library as demonstrated
above. You can read more about the :doc:`Validation library <../libraries/validation>`.

If the validation fails, we call the ``new()`` method you just created and return
the HTML form.

Save the News Item
^^^^^^^^^^^^^^^^^^

If the validation passed all the rules, we get the validated data by
:ref:`$this->validator->getValidated() <validation-getting-validated-data>` and
set them in the ``$post`` variable.

The ``NewsModel`` is loaded and called. This takes care of passing the news item
into the model. The :ref:`model-save` method handles inserting or updating the
record automatically, based on whether it finds an array key matching the primary
key.

This contains a new function :php:func:`url_title()`. This function -
provided by the :doc:`URL helper <../helpers/url_helper>` - strips down
the string you pass it, replacing all spaces by dashes (``-``) and makes
sure everything is in lowercase characters. This leaves you with a nice
slug, perfect for creating URIs.

Return Success Page
^^^^^^^^^^^^^^^^^^^

After this, view files are loaded and returned to display a success message.
Create a view at **app/Views/news/success.php** and write a success message.

This could be as simple as::

    <p>News item created successfully.</p>

NewsModel Updating
******************

The only thing that remains is ensuring that your model is set up
to allow data to be saved properly. The ``save()`` method that was
used will determine whether the information should be inserted
or if the row already exists and should be updated, based on the presence
of a primary key. In this case, there is no ``id`` field passed to it,
so it will insert a new row into it's table, ``news``.

However, by default the insert and update methods in the Model will
not actually save any data because it doesn't know what fields are
safe to be updated. Edit the ``NewsModel`` to provide it a list of updatable
fields in the ``$allowedFields`` property.

.. literalinclude:: create_news_items/003.php

This new property now contains the fields that we allow to be saved to the
database. Notice that we leave out the ``id``? That's because you will almost
never need to do that, since it is an auto-incrementing field in the database.
This helps protect against Mass Assignment Vulnerabilities. If your model is
handling your timestamps, you would also leave those out.

Create a News Item
******************

Now point your browser to your local development environment where you
installed CodeIgniter and add **/news/new** to the URL.
Add some news and check out the different pages you made.

.. image:: ../images/tutorial3.png
    :align: center
    :height: 415px
    :width: 45%

.. image:: ../images/tutorial4.png
    :align: center
    :height: 415px
    :width: 45%

Congratulations
***************

You just completed your first CodeIgniter4 application!

The diagram underneath shows your project's **app** folder, with all of the
files that you created or modified.

.. code-block:: none

    app/
    ├── Config
    │   ├── Filters.php (Modified)
    │   └── Routes.php  (Modified)
    ├── Controllers
    │   ├── News.php
    │   └── Pages.php
    ├── Models
    │   └── NewsModel.php
    └── Views
        ├── news
        │   ├── create.php
        │   ├── index.php
        │   ├── success.php
        │   └── view.php
        ├── pages
        │   ├── about.php
        │   └── home.php
        └── templates
            ├── footer.php
            └── header.php
