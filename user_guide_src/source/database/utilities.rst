########################
Utilities
########################

The Database Utility Class contains methods that help you manage your database.

.. contents::
    :local:
    :depth: 2

*******************
Get XML FROM Result
*******************

**getXMLFromResult()**

This method returns the xml result from database result. You can do like this:

.. literalinclude:: utilities/001.php
   :lines: 2-

and it will get the following xml result::

    <root>
        <element>
            <id>1</id>
            <name>bar</name>
        </element>
    </root>
