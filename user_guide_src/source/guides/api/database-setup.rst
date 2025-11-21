.. _ci47-rest-part3:

Creating the Database and Model
################################

.. contents::
    :local:
    :depth: 2

In this section we will get the data layer setup by creating a SQLite database table for our ``books`` resource, seed it with some sample data, and define a model to access it. By the end, you’ll have a working ``books`` table populated with example rows.

Create the Migrations
=====================

The migrations let you version-control your database schema by telling what changes to make to the database, and what changes to undo if we need to roll it back.  Let’s make one for simple ``authors`` and ``books`` tables.

Run the Spark command:

.. code-block:: console

    php spark make:migration CreateAuthorsTable
    php spark make:migration CreateBooksTable

This creates a new file under ``app/Database/Migrations/``.

Edit the CreateAuthorsTable file to look like this:

.. literalinclude:: code/004.php

Each author simply has a name for our purposes. We have made the name a uncommented unique key to prevent duplicates.

Now edit the CreateBooksTable file to look like this:

.. literalinclude:: code/005.php

You'll see this contains a foreign key reference to the ``authors`` table. This allows us to safely associate each book with an author and only maintain author names in one place.

Now run the migration:

.. code-block:: console

    php spark migrate

Now the database has the necessary structure to hold our books and authors.

Create a seeder
================

Seeders let you load sample data for development so you have something to work with right away. In this case, we’ll create a seeder to add some example books and their authors.

Run:

.. code-block:: console

    php spark make:seeder BookSeeder

Edit the file at ``app/Database/Seeds/BookSeeder.php``:

.. literalinclude:: code/006.php

This seeder first inserts authors into the ``authors`` table, capturing their IDs, then uses those IDs to insert books into the ``books`` table.

Then run the seeder:

.. code-block:: console

    php spark db:seed BookSeeder

You should see confirmation messages indicating the rows were inserted.

Create the Book model
=====================

Models make database access simple and reusable by providing an object-oriented interface to your tables and a fluent method for querying. Let's create a model for the ``authors`` and ``books`` tables.

Generate one:

.. code-block:: console

   php spark make:model AuthorModel
   php spark make:model BookModel

Both of the models will be simple extensions of CodeIgniter's base Model class.

Edit ``app/Models/AuthorModel.php``:

.. literalinclude:: code/007.php

Edit ``app/Models/BookModel.php``:

.. literalinclude:: code/008.php

This tells CodeIgniter which table to use and which fields can be mass-assigned.


In the next section, we’ll use your new model to power a RESTful API controller.
You’ll build the ``/api/books`` endpoint and see how CodeIgniter’s ``Api\ResponseTrait`` makes CRUD operations easy.
