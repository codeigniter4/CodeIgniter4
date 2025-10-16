.. _api_transformers:

#############
API Resources
#############

When building APIs, you often need to transform your data models into a consistent format before sending
them to the client. API Resources, implemented through transformers, provide a clean way to convert your
entities, arrays, or objects into structured API responses. They help separate your internal data structure
from what you expose through your API, making it easier to maintain and evolve your API over time.

.. contents::
    :local:
    :depth: 2

*****************
Quick Example
*****************

The following example shows a common usage pattern for transformers in your application.

.. literalinclude:: api_transformers/001.php

In this example, the ``UserTransformer`` defines which fields from a user entity should be included in the
API response. The ``transform()`` method converts a single resource, while ``transformMany()`` handles
collections of resources.

**********************
Creating a Transformer
**********************

To create a transformer, extend the ``BaseTransformer`` class and implement the ``toArray()`` method to
define your API resource structure. The ``toArray()`` method receives the resource being transformed as
a parameter, allowing you to access and transform its data.

Basic Transformer
=================

.. literalinclude:: api_transformers/002.php

The ``toArray()`` method receives the resource (entity, array, or object) as its parameter and defines
the structure of your API response. You can include any fields you want from the resource, and you can
also rename or transform values as needed.

Generating Transformer Files
=============================

CodeIgniter provides a CLI command to quickly generate transformer skeleton files:

.. code-block:: console

    php spark make:transformer User

This creates a new transformer file at **app/Transformers/User.php** with the basic structure already in place.

Command Options
---------------

The ``make:transformer`` command supports several options:

**--suffix**
    Appends "Transformer" to the class name:

    .. code-block:: console

        php spark make:transformer User --suffix

    Creates **app/Transformers/UserTransformer.php**

**--namespace**
    Specifies a custom root namespace:

    .. code-block:: console

        php spark make:transformer User --namespace="MyCompany\\API"

**--force**
    Forces overwriting an existing file:

    .. code-block:: console

        php spark make:transformer User --force

Subdirectories
--------------

You can organize transformers into subdirectories by including the path in the name:

.. code-block:: console

    php spark make:transformer api/v1/User

This creates **app/Transformers/Api/V1/User.php** with the appropriate namespace ``App\Transformers\Api\V1``.

Using Transformers in Controllers
==================================

Once you've created a transformer, you can use it in your controllers to transform data before returning
it to the client.

.. literalinclude:: api_transformers/003.php

***********************
Field Filtering
***********************

The transformer automatically supports field filtering through the ``fields`` query parameter of the current URL.
This allows API clients to request only specific fields they need, reducing bandwidth and improving performance.

.. literalinclude:: api_transformers/007.php

A request to ``/users/1?fields=id,name`` would return only:

.. code-block:: json

    {
        "id": 1,
        "name": "John Doe"
    }

Restricting Available Fields
=============================

By default, clients can request any field defined in your ``toArray()`` method. You can restrict which
fields are allowed by overriding the ``getAllowedFields()`` method:

.. literalinclude:: api_transformers/008.php

Now, even if a client requests ``/users/1?fields=email``, an ``ApiException`` will be thrown because
``email`` is not in the allowed fields list.

***************************
Including Related Resources
***************************

Transformers support loading of related resources through the ``include`` query parameter. This
follows a common API pattern where clients can specify which relationships they want included.
While relationships are the most frequent use case, you can include any additional data
you want by defining custom include methods.

Defining Include Methods
=========================

To support including related resources, create methods prefixed with ``include`` followed by the
resource name. Inside these methods, you can access the current resource being transformed via
``$this->resource``:

.. literalinclude:: api_transformers/009.php

Note how the include methods use ``$this->resource['id']`` to access the ID of the user being transformed.
The ``$this->resource`` property is automatically set by the transformer when ``transform()`` is called.

Clients can now request: ``/users/1?include=posts,comments``

The response would include:

.. code-block:: json

    {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "posts": [
            {
                "id": 1,
                "title": "First Post"
            }
        ],
        "comments": [
            {
                "id": 1,
                "content": "Great article!"
            }
        ]
    }

Restricting Available Includes
===============================

Similar to field filtering, you can restrict which relationships can be included by overriding the
``getAllowedIncludes()`` method:

.. literalinclude:: api_transformers/010.php

If you want to disable all includes, return an empty array:

.. literalinclude:: api_transformers/011.php

Include Validation
==================

The transformer automatically validates that any requested includes have corresponding ``include*()`` methods
defined in your transformer class. If a client requests an include that doesn't exist, an ``ApiException``
will be thrown.

For example, if a client requests::

    GET /api/users?include=invalid

And your transformer doesn't have an ``includeInvalid()`` method, an exception will be thrown with the message:
"Missing include method for: invalid".

This helps catch typos and prevents unexpected behavior.

************************
Transforming Collections
************************

The ``transformMany()`` method makes it easy to transform arrays of resources:

.. literalinclude:: api_transformers/012.php

The ``transformMany()`` method applies the same transformation logic to each item in the collection,
including any field filtering or includes specified in the request.

*********************************
Working with Different Data Types
*********************************

Transformers can handle various data types, not just entities.

Transforming Entities
=====================

When you pass an ``Entity`` instance to ``transform()``, it automatically calls the entity's ``toArray()``
method to get the data:

.. literalinclude:: api_transformers/013.php

Transforming Arrays
===================

You can transform plain arrays as well:

.. literalinclude:: api_transformers/014.php

Transforming Objects
====================

Any object can be cast to an array and transformed:

.. literalinclude:: api_transformers/015.php

Using toArray() Only
====================

If you don't pass a resource to ``transform()``, it will use the data from your ``toArray()`` method:

.. literalinclude:: api_transformers/016.php

***************
Class Reference
***************

.. php:namespace:: CodeIgniter\API

.. php:class:: BaseTransformer

    .. php:method:: __construct(?IncomingRequest $request = null)

        :param IncomingRequest|null $request: Optional request instance. If not provided, the global request will be used.

        Initializes the transformer and extracts the ``fields`` and ``include`` query parameters from the request.

    .. php:method:: toArray(mixed $resource)

        :param mixed $resource: The resource being transformed (Entity, array, object, or null)
        :returns: The array representation of the resource
        :rtype: array

        This abstract method must be implemented by child classes to define the structure of the API resource.
        The resource parameter contains the data being transformed. Return an array with the fields you want
        to include in the API response, accessing data from the ``$resource`` parameter.

        .. literalinclude:: api_transformers/017.php

    .. php:method:: transform($resource = null)

        :param mixed $resource: The resource to transform (Entity, array, object, or null)
        :returns: The transformed array
        :rtype: array

        Transforms the given resource into an array by calling ``toArray()`` with the resource data.
        If ``$resource`` is ``null``, passes ``null`` to ``toArray()``.
        If it's an Entity, extracts its array representation first. Otherwise, casts it to an array.

        The resource is also stored in ``$this->resource`` so include methods can access it.

        The method automatically applies field filtering and includes based on query parameters.

        .. literalinclude:: api_transformers/018.php

    .. php:method:: transformMany(array $resources)

        :param array $resources: The array of resources to transform
        :returns: Array of transformed resources
        :rtype: array

        Transforms a collection of resources by calling ``transform()`` on each item. Field filtering and
        includes are applied consistently to all items.

        .. literalinclude:: api_transformers/019.php

    .. php:method:: getAllowedFields()

        :returns: Array of allowed field names, or ``null`` to allow all fields
        :rtype: array|null

        Override this method to restrict which fields can be requested via the ``fields`` query parameter.
        Return ``null`` (the default) to allow all fields from ``toArray()``. Return an array of field names
        to create a whitelist of allowed fields.

        .. literalinclude:: api_transformers/022.php

    .. php:method:: getAllowedIncludes()

        :returns: Array of allowed include names, or ``null`` to allow all includes
        :rtype: array|null

        Override this method to restrict which related resources can be included via the ``include`` query
        parameter. Return ``null`` (the default) to allow all includes that have corresponding methods.
        Return an array of include names to create a whitelist. Return an empty array to disable all includes.

        .. literalinclude:: api_transformers/023.php

*******************
Exception Reference
*******************

.. php:class:: ApiException

    .. php:staticmethod:: forInvalidFields(string $field)

        :param string $field: The invalid field name(s)
        :returns: ApiException instance
        :rtype: ApiException

        Thrown when a client requests a field via the ``fields`` query parameter that is not in the allowed
        fields list.

    .. php:staticmethod:: forInvalidIncludes(string $include)

        :param string $include: The invalid include name(s)
        :returns: ApiException instance
        :rtype: ApiException

        Thrown when a client requests an include via the ``include`` query parameter that is not in the
        allowed includes list.

    .. php:staticmethod:: forMissingInclude(string $include)

        :param string $include: The missing include method name
        :returns: ApiException instance
        :rtype: ApiException

        Thrown when a client requests an include via the ``include`` query parameter, but the corresponding
        ``include*()`` method does not exist in the transformer class. This validation ensures that all
        requested includes have proper handler methods defined.
