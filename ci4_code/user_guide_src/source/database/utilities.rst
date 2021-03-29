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

This method returns the xml result from database result. You can do like this::

    $model = new class extends \CodeIgniter\Model {
        protected $table      = 'foo';
        protected $primaryKey = 'id';
    };
    $db = \Closure::bind(function ($model) {
        return $model->db;
    }, null, $model)($model);

    $util = (new \CodeIgniter\Database\Database())->loadUtils($db);
    echo $util->getXMLFromResult($model->get());

and it will get the following xml result::

    <root>
        <element>
            <id>1</id>
            <name>bar</name>
        </element>
    </root>
