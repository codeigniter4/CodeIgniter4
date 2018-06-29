=====================
Honeypot Class
=====================

The Honeypot Class makes it possible to determine when a Bot makes a request to a CodeIgniter4 application,
If it's enabled in ``Application\Config\Filters.php`` file. This is done by attaching form fields to any form,
and this form field is hidden from human but accessible to Bot. When data is entered into the field it's 
assumed the request is coming from a Bot, then an execption is thrown.

.. contents:: Page Contents

Enabling Honeypot
=====================

To enable Honeypot changes has to be made to the ``Application\Config\Filters.php``. Just uncomment honeypot
from the ``$globals`` Array.::

    public $globals = [
            'before' => [
                //'honeypot'
                // 'csrf',
            ],
            'after'  => [
                'toolbar',
                //'honeypot'
            ]
        ];

Customizing Honeypot
=====================

Honeypot can be customized. It allows the following customization. Customization file can found in 
``Application\Config\Honeypot.php`` and ``.env``.

* ``Display``
* ``Label``
* ``Field Name``
* ``Template``

**Display**

Display can contain values of ``True`` or ``False``, meaning display the template and hide the template
respectively. The value for display is called ``hidden``.::

    public $hidden = true;

The above is for ``Application\Config\Honeypot.php``.::

    honeypot.hidden = 'true'

The above is for ``.env``

**Label**

This the label for the input field. The value for label is called ``label``.::

    public $label = 'Fill This Field';

The above is for ``Application\Config\Honeypot.php``.::

    honeypot.label = 'Fill This Field'

The above is for ``.env``

**Field Name**

This the field name for the input field. The value for the field name is called ``name``.::

    public $name = 'honeypot';

The above is for ``Application\Config\Honeypot.php``.::

    honeypot.name = 'honeypot'

The above is for ``.env``

**Template**

This is the template of the honeypot. The value for the template is called ``template``.::

    public $template = '<label>{label}</label><input type="text" name="{name}" value=""/>';

The above is for ``Application\Config\Honeypot.php``.::

    honeypot.template = '<label>{label}</label><input type="text" name="{name}" value=""/>'

The above is for ``.env``