#########
Utilities
#########

The Database Utility Class contains methods that help you manage your database.

.. contents::
    :local:
    :depth: 2


Export a Query Result as an XML Document
========================================

Permits you to generate an XML file from a query result. The first
parameter expects a query result object, the second may contain an
optional array of config parameters. Example:

.. literalinclude:: utilities/001.php

and it will get the following xml result when the ``mytable`` has columns ``id`` and ``name``::

    <root>
        <element>
            <id>1</id>
            <name>bar</name>
        </element>
    </root>

.. important:: This method will NOT write the XML file for you. It
    simply creates the XML layout. If you need to write the file
    use the :php:func:`write_file()` helper.

