.. _ci47-rest-part3:

Creating the Database and Model
###############################

.. contents::
    :local:
    :depth: 2

In this section, we set up the data layer by creating a SQLite database table for our ``books`` resource, seeding it with sample data, and defining a model to access it. By the end, you'll have a working ``books`` table populated with example rows.

Create the Migrations
=====================

Migrations let you version-control your database schema by defining what to apply and how to roll it back. Let's make ones for simple ``authors`` and ``books`` tables.

Run the Spark command:

.. code-block:: console

    php spark make:migration CreateAuthorsTable
    php spark make:migration CreateBooksTable

This creates a new file under **app/Database/Migrations/**.

Edit **app/Database/Migrations/CreateAuthorsTable.php** to look like this:

.. literalinclude:: code/004.php

Each author only needs a name for our purposes. We've made the name a unique key to prevent duplicates.

Now, edit **app/Database/Migrations/CreateBooksTable.php** to look like this:

.. literalinclude:: code/005.php

This contains a foreign key reference to the ``authors`` table. It lets us associate each book with an author and keep author names in one place.

Now run the migration:

.. code-block:: console

    php spark migrate

Now, the database has the structure needed to hold our book and author records.

Create a seeder
===============

Seeders load sample data for development so you have something to work with right away. Here, we'll add some example books and their authors.

Run:

.. code-block:: console

    php spark make:seeder BookSeeder

Edit the file at **app/Database/Seeds/BookSeeder.php**:

.. literalinclude:: code/006.php

This seeder first inserts authors into the ``authors`` table, captures their IDs, and then uses those IDs to insert books into the ``books`` table.

Then run the seeder:

.. code-block:: console

    php spark db:seed BookSeeder

You should see confirmation messages indicating the rows were inserted.

Create the Book model
=====================

Models make database access simple and reusable by providing an object-oriented interface to your tables and a fluent query API. Let's create models for the ``authors`` and ``books`` tables.

Generate one:

.. code-block:: console

   php spark make:model AuthorModel
   php spark make:model BookModel

Both models will be simple extensions of CodeIgniter's base Model class.

Edit **app/Models/AuthorModel.php**:

.. literalinclude:: code/007.php

Edit **app/Models/BookModel.php**:

.. literalinclude:: code/008.php

This tells CodeIgniter which table to use and which fields can be mass-assigned.

In the next section, we'll use your new models to power a RESTful API controller.
You'll build the ``/api/books`` endpoint and see how CodeIgniter's ``Api\ResponseTrait`` makes CRUD operations easy.
