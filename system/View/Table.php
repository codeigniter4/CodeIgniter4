<?php
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2019, British Columbia Institute of Technology
 * Copyright (c) 2019-2020 CodeIgniter Foundation
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package    CodeIgniter
 * @author     EllisLab Dev Team
 * @copyright  Copyright (c) 2008 - 2014, EllisLab, Inc. (https://ellislab.com/)
 * @copyright  Copyright (c) 2014 - 2019, British Columbia Institute of Technology (https://bcit.ca/)
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link       https://codeigniter.com
 * @since      Version 1.3.1
 * @filesource
 */

namespace CodeIgniter\View;

use CodeIgniter\Database\BaseResult;

/**
 * HTML Table Generating Class
 *
 * Lets you create tables manually or from database result objects, or arrays.
 *
 * @package    CodeIgniter
 * @subpackage Libraries
 * @category   HTML Tables
 * @author     EllisLab Dev Team
 */
class Table
{

	/**
	 * Data for table rows
	 *
	 * @var array
	 */
	public $rows = [];

	/**
	 * Data for table heading
	 *
	 * @var array
	 */
	public $heading = [];

	/**
	 * Data for table footing
	 *
	 * @var array
	 */
	public $footing = [];

	/**
	 * Whether or not to automatically create the table header
	 *
	 * @var boolean
	 */
	public $autoHeading = true;

	/**
	 * Table caption
	 *
	 * @var string
	 */
	public $caption;

	/**
	 * Table layout template
	 *
	 * @var array
	 */
	public $template;

	/**
	 * Newline setting
	 *
	 * @var string
	 */
	public $newline = "\n";

	/**
	 * Contents of empty cells
	 *
	 * @var string
	 */
	public $emptyCells = '';

	/**
	 * Callback for custom table layout
	 *
	 * @var function
	 */
	public $function;

	/**
	 * Set the template from the table config file if it exists
	 *
	 * @param  array $config (default: array())
	 * @return void
	 */
	public function __construct($config = [])
	{
		// initialize config
		foreach ($config as $key => $val)
		{
			$this->template[$key] = $val;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Set the template
	 *
	 * @param  array $template
	 * @return boolean
	 */
	public function setTemplate($template)
	{
		if (! is_array($template))
		{
			return false;
		}

		$this->template = $template;
		return true;
	}

	// --------------------------------------------------------------------

	/**
	 * Set the table heading
	 *
	 * Can be passed as an array or discreet params
	 *
	 * @return Table
	 */
	public function setHeading()
	{
		$this->heading = $this->_prepArgs(func_get_args());
		return $this;
	}

	/**
	 * Set the table footing
	 *
	 * Can be passed as an array or discreet params
	 *
	 * @return Table
	 */
	public function setFooting()
	{
		$this->footing = $this->_prepArgs(func_get_args());
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Set columns. Takes a one-dimensional array as input and creates
	 * a multi-dimensional array with a depth equal to the number of
	 * columns. This allows a single array with many elements to be
	 * displayed in a table that has a fixed column count.
	 *
	 * @param  array   $array
	 * @param  integer $columnLimit
	 * @return array
	 */
	public function makeColumns($array = [], $columnLimit = 0)
	{
		if (! is_array($array) || count($array) === 0 || ! is_int($columnLimit))
		{
			return false;
		}

		// Turn off the auto-heading feature since it's doubtful we
		// will want headings from a one-dimensional array
		$this->autoHeading = false;

		if ($columnLimit === 0)
		{
			return $array;
		}

		$new = [];
		do
		{
			$temp = array_splice($array, 0, $columnLimit);

			if (count($temp) < $columnLimit)
			{
				for ($i = count($temp); $i < $columnLimit; $i ++)
				{
					$temp[] = '&nbsp;';
				}
			}

			$new[] = $temp;
		}
		while (count($array) > 0);

		return $new;
	}

	// --------------------------------------------------------------------

	/**
	 * Set "empty" cells
	 *
	 * Can be passed as an array or discreet params
	 *
	 * @param  mixed $value
	 * @return Table
	 */
	public function setEmpty($value)
	{
		$this->emptyCells = $value;
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Add a table row
	 *
	 * Can be passed as an array or discreet params
	 *
	 * @return Table
	 */
	public function addRow()
	{
		$this->rows[] = $this->_prepArgs(func_get_args());
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Prep Args
	 *
	 * Ensures a standard associative array format for all cell data
	 *
	 * @param  array
	 * @return array
	 */
	protected function _prepArgs($args)
	{
		// If there is no $args[0], skip this and treat as an associative array
		// This can happen if there is only a single key, for example this is passed to table->generate
		// array(array('foo'=>'bar'))
		if (isset($args[0]) && count($args) === 1 && is_array($args[0]) && ! isset($args[0]['data']))
		{
			$args = $args[0];
		}

		foreach ($args as $key => $val)
		{
			is_array($val) || $args[$key] = ['data' => $val];
		}

		return $args;
	}

	// --------------------------------------------------------------------

	/**
	 * Add a table caption
	 *
	 * @param  string $caption
	 * @return Table
	 */
	public function setCaption($caption)
	{
		$this->caption = $caption;
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Generate the table
	 *
	 * @param  mixed $tableData
	 * @return string
	 */
	public function generate($tableData = null)
	{
		// The table data can optionally be passed to this function
		// either as a database result object or an array
		if (! empty($tableData))
		{
			if ($tableData instanceof BaseResult)
			{
				$this->_setFromDBResult($tableData);
			}
			elseif (is_array($tableData))
			{
				$this->_setFromArray($tableData);
			}
		}

		// Is there anything to display? No? Smite them!
		if (empty($this->heading) && empty($this->rows))
		{
			return 'Undefined table data';
		}

		// Compile and validate the template date
		$this->_compileTemplate();

		// Validate a possibly existing custom cell manipulation function
		if (isset($this->function) && ! is_callable($this->function))
		{
			$this->function = null;
		}

		// Build the table!

		$out = $this->template['table_open'] . $this->newline;

		// Add any caption here
		if ($this->caption)
		{
			$out .= '<caption>' . $this->caption . '</caption>' . $this->newline;
		}

		// Is there a table heading to display?
		if (! empty($this->heading))
		{
			$out .= $this->template['thead_open'] . $this->newline . $this->template['heading_row_start'] . $this->newline;

			foreach ($this->heading as $heading)
			{
				$temp = $this->template['heading_cell_start'];

				foreach ($heading as $key => $val)
				{
					if ($key !== 'data')
					{
						$temp = str_replace('<th', '<th ' . $key . '="' . $val . '"', $temp);
					}
				}

				$out .= $temp . (isset($heading['data']) ? $heading['data'] : '') . $this->template['heading_cell_end'];
			}

			$out .= $this->template['heading_row_end'] . $this->newline . $this->template['thead_close'] . $this->newline;
		}

		// Build the table rows
		if (! empty($this->rows))
		{
			$out .= $this->template['tbody_open'] . $this->newline;

			$i = 1;
			foreach ($this->rows as $row)
			{
				// We use modulus to alternate the row colors
				$name = fmod($i ++, 2) ? '' : 'alt_';

				$out .= $this->template['row_' . $name . 'start'] . $this->newline;

				foreach ($row as $cell)
				{
					$temp = $this->template['cell_' . $name . 'start'];

					foreach ($cell as $key => $val)
					{
						if ($key !== 'data')
						{
							$temp = str_replace('<td', '<td ' . $key . '="' . $val . '"', $temp);
						}
					}

					$cell = isset($cell['data']) ? $cell['data'] : '';
					$out .= $temp;

					if ($cell === '' || $cell === null)
					{
						$out .= $this->emptyCells;
					}
					elseif (isset($this->function))
					{
						$out .= call_user_func($this->function, $cell);
					}
					else
					{
						$out .= $cell;
					}

					$out .= $this->template['cell_' . $name . 'end'];
				}

				$out .= $this->template['row_' . $name . 'end'] . $this->newline;
			}
		}

		// Any table footing to display?
		if (! empty($this->footing))
		{
			$out .= $this->template['tfoot_open'] . $this->newline . $this->template['footing_row_start'] . $this->newline;

			foreach ($this->footing as $footing)
			{
				$temp = $this->template['footing_cell_start'];

				foreach ($footing as $key => $val)
				{
					if ($key !== 'data')
					{
						$temp = str_replace('<th', '<th ' . $key . '="' . $val . '"', $temp);
					}
				}

				$out .= $temp . (isset($footing['data']) ? $footing['data'] : '') . $this->template['footing_cell_end'];
			}

			$out .= $this->template['footing_row_end'] . $this->newline . $this->template['tfoot_close'] . $this->newline;
		}

		$out .= $this->template['tbody_close'] . $this->newline;

		// And finally, close off the table
		$out .= $this->template['table_close'];

		// Clear table class properties before generating the table
		$this->clear();

		return $out;
	}

	// --------------------------------------------------------------------

	/**
	 * Clears the table arrays.  Useful if multiple tables are being generated
	 *
	 * @return Table
	 */
	public function clear()
	{
		$this->rows        = [];
		$this->heading     = [];
		$this->footing     = [];
		$this->autoHeading = true;
		$this->caption     = null;
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Set table data from a database result object
	 *
	 * @param  BaseResult $object Database result object
	 * @return void
	 */
	protected function _setFromDBResult($object)
	{
		// First generate the headings from the table column names
		if ($this->autoHeading === true && empty($this->heading))
		{
			$this->heading = $this->_prepArgs($object->getFieldNames());
		}

		foreach ($object->getResultArray() as $row)
		{
			$this->rows[] = $this->_prepArgs($row);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Set table data from an array
	 *
	 * @param  array $data
	 * @return void
	 */
	protected function _setFromArray($data)
	{
		if ($this->autoHeading === true && empty($this->heading))
		{
			$this->heading = $this->_prepArgs(array_shift($data));
		}

		foreach ($data as &$row)
		{
			$this->rows[] = $this->_prepArgs($row);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Compile Template
	 *
	 * @return void
	 */
	protected function _compileTemplate()
	{
		if ($this->template === null)
		{
			$this->template = $this->_defaultTemplate();
			return;
		}

		$this->temp = $this->_defaultTemplate();
		foreach (['table_open', 'thead_open', 'thead_close', 'heading_row_start', 'heading_row_end', 'heading_cell_start', 'heading_cell_end', 'tbody_open', 'tbody_close', 'row_start', 'row_end', 'cell_start', 'cell_end', 'row_alt_start', 'row_alt_end', 'cell_alt_start', 'cell_alt_end', 'table_close'] as $val)
		{
			if (! isset($this->template[$val]))
			{
				$this->template[$val] = $this->temp[$val];
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Default Template
	 *
	 * @return array
	 */
	protected function _defaultTemplate()
	{
		return [
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
			'table_close'        => '</table>',
		];
	}

	// --------------------------------------------------------------------
}
