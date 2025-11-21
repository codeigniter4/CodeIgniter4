.. _ci47-rest-part4:

Building a RESTful Controller
#############################

In this chapter we will build out the API endpoints to expose your ``books`` table through a proper RESTful API.
We'll use :php:class:`CodeIgniter\\RESTful\\ResourceController` to handle CRUD actions with almost no boilerplate.

What is RESTful?
================

RESTful APIs use standard HTTP verbs (GET, POST, PUT, DELETE) to perform actions on resources identified by URIs. This approach makes APIs predictable and easy to use. There can be much debate ont some of the finer points of what makes a REST API, but following these basics can get it close enough for many uses. By using auto-routing and the :php:class:`Api\ResponseTrait`, CodeIgniter makes it simple to create RESTful endpoints.

Generate the controller
=======================

Run the Spark command:

.. code-block:: console

    php spark make:controller Api/Books

This creates ``app/Controllers/Api/Books.php``.

Open it and replace its contents with the following stubbed out class:

.. literalinclude:: code/009.php

Since we're using auto-routing, we need to use the ``index`` method names so it doesn't interfere with mapping to the URI segments. But we can use the HTTP verb prefixes (get, post, put, delete) to indicate which method handles which verb in a clear manner. The only one that is slightly odd is ``getIndex()`` which must be used to map to both listing all resources and listing a single resource by ID.

.. tip::

   If you prefer a different naming scheme, you would need to define routes explicitly in ``app/Config/Routes.php`` and turn auto-routing off.

API Transformers
=================

It is considered a best practice to separate your data models from the way they are presented in your API responses. This is often done using transformers or resource classes that format the data consistently. CodeIgniter provides API transformers to help with this.

Create the transformers with the generator command:

.. code-block:: console

   php spark make:transformer BookTransformer

The transormer requires a single method, ``toArray`` to be present and accept a mixed data type called ``$resource``. This method is responsible for transforming the resource into an array format suitable for API responses. The returned array is what is then encoded as JSON or XML for the API response.

Edit the Book transformer at ``app/Transformers/BookTransformer.php``. This one is a little more complex since it includes related author data:

.. literalinclude:: code/011.php

One feature of transformers is the ability to include related resources conditionally. In this case, we check if the ``author`` relationship is loaded on the book resource before including it in the response. This allows for flexibility in how much data is returned based on the context of the request. The client calling the API would have to request the related data explicitly, through query parameters, like: ``/api/book?include=author``. The method name must start with ``include`` followed by the related resource name with the first letter capitalized.

You have probably noticed that we did not use an AuthorTransformer. This is because the author data is simple enough that we can return it directly without additional transformation. However, for more complex related resources, you might want to create separate transformers for them as well. Additionally, we will collect the author information at query time so that we don't hit any N+1 query issues later.

Listing Books
=============

We made the ``$id`` parameter optional so that the same method can handle both listing all books and retrieving a single book by ID. Let's implement that now.

.. literalinclude:: code/012.php
    :language: php
    :lines: 15-41

In this method, we check if an ``$id`` is provided. If it is, we attempt to find the specific book. If we could not find the book by that ID, we return a 404 Not Found response using the :php:meth:`failNotFound()` helper from :php:trait:`ResponseTrait`. If we do find the book, we use our BookTransformer and return the formatted response.

If no ``$id`` is provided, we use the model to retrieve all books, stopping short of actually retrieving the records. This allows us to use the ``ResponseTrait``'s :php:meth:`paginate` method to handle pagination automatically. We pass the name of the transformer to the ``paginate`` method so that it can format each book in the paginated result set.

In both of these cases, we use a new method on the model called ``withAuthorInfo()``. This is a custom method we will add to the model to join the related author data when retrieving books.

Add the Model helper method
---------------------------

In your BookModel, we add a new method called ``withAuthorInfo()``. This method uses the Query Builder to join the ``authors`` table and select the relevant author fields. This way, when we retrieve books, we also get the associated author information without needing to make separate queries for each book.

.. literalinclude:: code/014.php


Test the list all endpoint
--------------------------

Start the local server:

.. code-block:: console

   php spark serve

Now visit:

- **Browser:** ``http://localhost:8080/api/book``
- **cURL:** ``curl http://localhost:8080/api/book``

You should see a paginated list of books in JSON format:

.. code-block:: json

    {
        "data": [
            {
                "id": 1,
                "title": "Dune",
                "author": "Frank Herbert",
                "year": 1965,
                "created_at": "2025-11-08 00:00:00",
                "updated_at": "2025-11-08 00:00:00"
            },
            {
                "id": 2,
                "title": "Neuromancer",
                "author": "William Gibson",
                "year": 1984,
                "created_at": "2025-11-08 00:00:00",
                "updated_at": "2025-11-08 00:00:00"
            }
        ],
        "meta": {
            "page": 1,
            "perPage": 20,
            "total": 2,
            "totalPages": 1
        },
        "links": {
            "self": "http://localhost:8080/api/book?page=1",
            "first": "http://localhost:8080/api/book?page=1",
            "last": "http://localhost:8080/api/book?page=1"
            "prev": null,
            "next": null,
        }
    }

If you see JSON data from your seeder, congratulations—your API is live!

Implement the remaining methods
===============================

Edit ``app/Controllers/Api/Book.php`` to include the remaining methods:

.. literalinclude:: code/012.php

Each method uses helpers from :php:trait:`ResponseTrait` to send proper HTTP status codes and JSON payloads.

And that's it! You now have a fully functional RESTful API for managing books, complete with proper HTTP methods, status codes, and data transformation. You can further enhance this API by adding authentication, validation, and other features as needed.

A More Semantic Name scheme
============================

In the previous examples, we used method names like ``getIndex``, ``putIndex``, etc because we wanted to solely rely on the HTTP verb to determine the action. With auto-routing enabled, we have to use the ``index`` method name to avoid conflicts with URI segments. However, if you prefer more semantic method names, you could change the method names so that they reflect the action being performed, such as ``getList``, ``postCreate``, ``putUpdate``, and ``deleteDelete``. This would then make each method's purpose clearer at a glance. And would just add one new segment to the URI.

```
GET    /api/book/list        -> getList()
POST   /api/book/create      -> postCreate()
PUT    /api/book/update/(:id) -> putUpdate($id)
DELETE /api/book/delete/(:id) -> deleteDelete($id)
```
