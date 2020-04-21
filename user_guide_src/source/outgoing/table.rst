################
HTML Table Class
################

The Table Class provides methods that enable you to auto-generate HTML
tables from arrays or database result sets.

.. contents::
  :local:

.. raw:: html

  <div class="custom-index container"></div>

*********************
Using the Table Class
*********************

Initializing the Class
======================

The Table class is not provided as a service, and should be instantiated
"normally", for instance::

	$table = new \CodeIgniter\View\Table();

Examples
========

Here is an example showing how you can create a table from a
multi-dimensional array. Note that the first array index will become the
table heading (or you can set your own headings using the ``setHeading()``
method described in the function reference below).

::

	$table = new \CodeIgniter\View\Table();

	$data = [
		['Name', 'Color', 'Size'],
		['Fred', 'Blue',  'Small'],
		['Mary', 'Red',   'Large'],
		['John', 'Green', 'Medium'],
	];

	echo $table->generate($data);

Here is an example of a table created from a database query result. The
table class will automatically generate the headings based on the table
names (or you can set your own headings using the ``setHeading()``
method described in the class reference below).

::

	$table = new \CodeIgniter\View\Table();

	$query = $db->query('SELECT * FROM my_table');

	echo $table->generate($query);

Here is an example showing how you might create a table using discrete
parameters::

	$table = new \CodeIgniter\View\Table();

	$table->setHeading('Name', 'Color', 'Size');

	$table->addRow('Fred', 'Blue', 'Small');
	$table->addRow('Mary', 'Red', 'Large');
	$table->addRow('John', 'Green', 'Medium');

	echo $table->generate();

Here is the same example, except instead of individual parameters,
arrays are used::

	$table = new \CodeIgniter\View\Table();

	$table->setHeading(array('Name', 'Color', 'Size'));

	$table->addRow(['Fred', 'Blue', 'Small']);
	$table->addRow(['Mary', 'Red', 'Large']);
	$table->addRow(['John', 'Green', 'Medium']);

	echo $table->generate();

Changing the Look of Your Table
===============================

The Table Class permits you to set a table template with which you can
specify the design of your layout. Here is the template prototype::

	$template = [
		'table_open'         => '<table border="0" cellpadding="4" cellspacing="0">',

		'thead_open'         => '<thead>',
		'thead_close'        => '</thead>',

		'heading_row_start'  => '<tr>',
		'heading_row_end'    => '</tr>',
		'heading_cell_start' => '<th>',
		'heading_cell_end'   => '</th>',

		'tfoot_open'         => '<tfoot>',
		'tfoot_close'        => '</tfoot>',

		'footing_row_start'  => '<tr>',
		'footing_row_end'    => '</tr>',
		'footing_cell_start' => '<td>',
		'footing_cell_end'   => '</td>',

		'tbody_open'         => '<tbody>',
		'tbody_close'        => '</tbody>',

		'row_start'          => '<tr>',
		'row_end'            => '</tr>',
		'cell_start'         => '<td>',
		'cell_end'           => '</td>',

		'row_alt_start'      => '<tr>',
		'row_alt_end'        => '</tr>',
		'cell_alt_start'     => '<td>',
		'cell_alt_end'       => '</td>',

		'table_close'        => '</table>'
	];

	$table->setTemplate($template);

.. note:: You'll notice there are two sets of "row" blocks in the
	template. These permit you to create alternating row colors or design
	elements that alternate with each iteration of the row data.

You are NOT required to submit a complete template. If you only need to
change parts of the layout you can simply submit those elements. In this
example, only the table opening tag is being changed::

	$template = [
		'table_open' => '<table border="1" cellpadding="2" cellspacing="1" class="mytable">'
	];

	$table->setTemplate($template);
	
You can also set defaults for these by passing an array of template settings
to the Table constructor.::

	$customSettings = [
		'table_open' => '<table border="1" cellpadding="2" cellspacing="1" class="mytable">'
	];

	$table = new \CodeIgniter\View\Table($customSettings);


***************
Class Reference
***************

.. php:class:: Table

	.. attribute:: $function = NULL

		Allows you to specify a native PHP function or a valid function array object to be applied to all cell data.
		::

			$table = new \CodeIgniter\View\Table();

			$table->setHeading('Name', 'Color', 'Size');
			$table->addRow('Fred', '<strong>Blue</strong>', 'Small');

			$table->function = 'htmlspecialchars';
			echo $table->generate();

		In the above example, all cell data would be run through PHP's :php:func:`htmlspecialchars()` function, resulting in::

			<td>Fred</td><td>&lt;strong&gt;Blue&lt;/strong&gt;</td><td>Small</td>

	.. php:method:: generate([$tableData = NULL])

		:param	mixed	$tableData: Data to populate the table rows with
		:returns:	HTML table
		:rtype:	string

		Returns a string containing the generated table. Accepts an optional parameter which can be an array or a database result object.

	.. php:method:: setCaption($caption)

		:param	string	$caption: Table caption
		:returns:	Table instance (method chaining)
		:rtype:	Table

		Permits you to add a caption to the table.
		::

			$table->setCaption('Colors');

	.. php:method:: setHeading([$args = [] [, ...]])

		:param	mixed	$args: An array or multiple strings containing the table column titles
		:returns:	Table instance (method chaining)
		:rtype:	Table

		Permits you to set the table heading. You can submit an array or discrete params::

			$table->setHeading('Name', 'Color', 'Size'); // or

			$table->setHeading(['Name', 'Color', 'Size']);

	.. php:method:: setFooting([$args = [] [, ...]])

		:param	mixed	$args: An array or multiple strings containing the table footing values
		:returns:	Table instance (method chaining)
		:rtype:	Table

		Permits you to set the table footing. You can submit an array or discrete params::

			$table->setFooting('Subtotal', $subtotal, $notes); // or

			$table->setFooting(['Subtotal', $subtotal, $notes]);

	.. php:method:: addRow([$args = array()[, ...]])

		:param	mixed	$args: An array or multiple strings containing the row values
		:returns:	Table instance (method chaining)
		:rtype:	Table

		Permits you to add a row to your table. You can submit an array or discrete params::

			$table->addRow('Blue', 'Red', 'Green'); // or

			$table->addRow(['Blue', 'Red', 'Green']);

		If you would like to set an individual cell's tag attributes, you can use an associative array for that cell.
		The associative key **data** defines the cell's data. Any other key => val pairs are added as key='val' attributes to the tag::

			$cell = ['data' => 'Blue', 'class' => 'highlight', 'colspan' => 2];
			$table->addRow($cell, 'Red', 'Green');

			// generates
			// <td class='highlight' colspan='2'>Blue</td><td>Red</td><td>Green</td>

	.. php:method:: makeColumns([$array = [] [, $columnLimit = 0]])

		:param	array	$array: An array containing multiple rows' data
		:param	int	$columnLimit: Count of columns in the table
		:returns:	An array of HTML table columns
		:rtype:	array

		This method takes a one-dimensional array as input and creates a multi-dimensional array with a depth equal to the number of columns desired.
		This allows a single array with many elements to be displayed in a table that has a fixed column count. Consider this example::

			$list = ['one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten', 'eleven', 'twelve'];

			$newList = $table->makeColumns($list, 3);

			$table->generate($newList);

			// Generates a table with this prototype

			<table border="0" cellpadding="4" cellspacing="0">
			<tr>
			<td>one</td><td>two</td><td>three</td>
			</tr><tr>
			<td>four</td><td>five</td><td>six</td>
			</tr><tr>
			<td>seven</td><td>eight</td><td>nine</td>
			</tr><tr>
			<td>ten</td><td>eleven</td><td>twelve</td></tr>
			</table>


	.. php:method:: setTemplate($template)

		:param	array	$template: An associative array containing template values
		:returns:	TRUE on success, FALSE on failure
		:rtype:	bool

		Permits you to set your template. You can submit a full or partial template.
		::

			$template = [
				'table_open'  => '<table border="1" cellpadding="2" cellspacing="1" class="mytable">'
			];
		
			$table->setTemplate($template);

	.. php:method:: setEmpty($value)

		:param	mixed	$value: Value to put in empty cells
		:returns:	Table instance (method chaining)
		:rtype:	Table

		Lets you set a default value for use in any table cells that are empty.
		You might, for example, set a non-breaking space::

			$table->setEmpty("&nbsp;");

	.. php:method:: clear()

		:returns:	Table instance (method chaining)
		:rtype:	Table

		Lets you clear the table heading, row data and caption. If
		you need to show multiple tables with different data you
		should to call this method after each table has been
		generated to clear the previous table information.

		Example ::

			$table = new \CodeIgniter\View\Table();


			$table->setCaption('Preferences')
                            ->setHeading('Name', 'Color', 'Size')
                            ->addRow('Fred', 'Blue', 'Small')
                            ->addRow('Mary', 'Red', 'Large')
                            ->addRow('John', 'Green', 'Medium');

			echo $table->generate();

			$table->clear();

			$table->setCaption('Shipping')
                            ->setHeading('Name', 'Day', 'Delivery')
                            ->addRow('Fred', 'Wednesday', 'Express')
                            ->addRow('Mary', 'Monday', 'Air')
                            ->addRow('John', 'Saturday', 'Overnight');

			echo $table->generate();
