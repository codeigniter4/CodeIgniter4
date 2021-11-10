<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\View;

use CodeIgniter\Database\BaseResult;

/**
 * HTML Table Generating Class
 *
 * Lets you create tables manually or from database result objects, or arrays.
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
     * @var bool
     */
    public $autoHeading = true;

    /**
     * Table caption
     *
     * @var string|null
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
     * @var callable|null
     */
    public $function;

    /**
     * Set the template from the table config file if it exists
     *
     * @param array $config (default: array())
     */
    public function __construct($config = [])
    {
        // initialize config
        foreach ($config as $key => $val) {
            $this->template[$key] = $val;
        }
    }

    /**
     * Set the template
     *
     * @param array $template
     *
     * @return bool
     */
    public function setTemplate($template)
    {
        if (! is_array($template)) {
            return false;
        }

        $this->template = $template;

        return true;
    }

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

    /**
     * Set columns. Takes a one-dimensional array as input and creates
     * a multi-dimensional array with a depth equal to the number of
     * columns. This allows a single array with many elements to be
     * displayed in a table that has a fixed column count.
     *
     * @param array $array
     * @param int   $columnLimit
     *
     * @return array|false
     */
    public function makeColumns($array = [], $columnLimit = 0)
    {
        if (! is_array($array) || $array === [] || ! is_int($columnLimit)) {
            return false;
        }

        // Turn off the auto-heading feature since it's doubtful we
        // will want headings from a one-dimensional array
        $this->autoHeading = false;

        if ($columnLimit === 0) {
            return $array;
        }

        $new = [];

        do {
            $temp = array_splice($array, 0, $columnLimit);

            if (count($temp) < $columnLimit) {
                for ($i = count($temp); $i < $columnLimit; $i++) {
                    $temp[] = '&nbsp;';
                }
            }

            $new[] = $temp;
        } while ($array !== []);

        return $new;
    }

    /**
     * Set "empty" cells
     *
     * Can be passed as an array or discreet params
     *
     * @param mixed $value
     *
     * @return Table
     */
    public function setEmpty($value)
    {
        $this->emptyCells = $value;

        return $this;
    }

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

    /**
     * Prep Args
     *
     * Ensures a standard associative array format for all cell data
     *
     * @return array
     */
    protected function _prepArgs(array $args)
    {
        // If there is no $args[0], skip this and treat as an associative array
        // This can happen if there is only a single key, for example this is passed to table->generate
        // array(array('foo'=>'bar'))
        if (isset($args[0]) && count($args) === 1 && is_array($args[0]) && ! isset($args[0]['data'])) {
            $args = $args[0];
        }

        foreach ($args as $key => $val) {
            if (! is_array($val)) {
                $args[$key] = ['data' => $val];
            }
        }

        return $args;
    }

    /**
     * Add a table caption
     *
     * @param string $caption
     *
     * @return Table
     */
    public function setCaption($caption)
    {
        $this->caption = $caption;

        return $this;
    }

    /**
     * Generate the table
     *
     * @param mixed $tableData
     *
     * @return string
     */
    public function generate($tableData = null)
    {
        // The table data can optionally be passed to this function
        // either as a database result object or an array
        if (! empty($tableData)) {
            if ($tableData instanceof BaseResult) {
                $this->_setFromDBResult($tableData);
            } elseif (is_array($tableData)) {
                $this->_setFromArray($tableData);
            }
        }

        // Is there anything to display? No? Smite them!
        if (empty($this->heading) && empty($this->rows)) {
            return 'Undefined table data';
        }

        // Compile and validate the template date
        $this->_compileTemplate();

        // Validate a possibly existing custom cell manipulation function
        if (isset($this->function) && ! is_callable($this->function)) {
            $this->function = null;
        }

        // Build the table!
        $out = $this->template['table_open'] . $this->newline;

        // Add any caption here
        if ($this->caption) {
            $out .= '<caption>' . $this->caption . '</caption>' . $this->newline;
        }

        // Is there a table heading to display?
        if (! empty($this->heading)) {
            $out .= $this->template['thead_open'] . $this->newline . $this->template['heading_row_start'] . $this->newline;

            foreach ($this->heading as $heading) {
                $temp = $this->template['heading_cell_start'];

                foreach ($heading as $key => $val) {
                    if ($key !== 'data') {
                        $temp = str_replace('<th', '<th ' . $key . '="' . $val . '"', $temp);
                    }
                }

                $out .= $temp . ($heading['data'] ?? '') . $this->template['heading_cell_end'];
            }

            $out .= $this->template['heading_row_end'] . $this->newline . $this->template['thead_close'] . $this->newline;
        }

        // Build the table rows
        if (! empty($this->rows)) {
            $out .= $this->template['tbody_open'] . $this->newline;

            $i = 1;

            foreach ($this->rows as $row) {
                // We use modulus to alternate the row colors
                $name = fmod($i++, 2) ? '' : 'alt_';

                $out .= $this->template['row_' . $name . 'start'] . $this->newline;

                foreach ($row as $cell) {
                    $temp = $this->template['cell_' . $name . 'start'];

                    foreach ($cell as $key => $val) {
                        if ($key !== 'data') {
                            $temp = str_replace('<td', '<td ' . $key . '="' . $val . '"', $temp);
                        }
                    }

                    $cell = $cell['data'] ?? '';
                    $out .= $temp;

                    if ($cell === '' || $cell === null) {
                        $out .= $this->emptyCells;
                    } elseif (isset($this->function)) {
                        $out .= ($this->function)($cell);
                    } else {
                        $out .= $cell;
                    }

                    $out .= $this->template['cell_' . $name . 'end'];
                }

                $out .= $this->template['row_' . $name . 'end'] . $this->newline;
            }

            $out .= $this->template['tbody_close'] . $this->newline;
        }

        // Any table footing to display?
        if (! empty($this->footing)) {
            $out .= $this->template['tfoot_open'] . $this->newline . $this->template['footing_row_start'] . $this->newline;

            foreach ($this->footing as $footing) {
                $temp = $this->template['footing_cell_start'];

                foreach ($footing as $key => $val) {
                    if ($key !== 'data') {
                        $temp = str_replace('<th', '<th ' . $key . '="' . $val . '"', $temp);
                    }
                }

                $out .= $temp . ($footing['data'] ?? '') . $this->template['footing_cell_end'];
            }

            $out .= $this->template['footing_row_end'] . $this->newline . $this->template['tfoot_close'] . $this->newline;
        }

        // And finally, close off the table
        $out .= $this->template['table_close'];

        // Clear table class properties before generating the table
        $this->clear();

        return $out;
    }

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

    /**
     * Set table data from a database result object
     *
     * @param BaseResult $object Database result object
     */
    protected function _setFromDBResult($object)
    {
        // First generate the headings from the table column names
        if ($this->autoHeading && empty($this->heading)) {
            $this->heading = $this->_prepArgs($object->getFieldNames());
        }

        foreach ($object->getResultArray() as $row) {
            $this->rows[] = $this->_prepArgs($row);
        }
    }

    /**
     * Set table data from an array
     *
     * @param array $data
     */
    protected function _setFromArray($data)
    {
        if ($this->autoHeading && empty($this->heading)) {
            $this->heading = $this->_prepArgs(array_shift($data));
        }

        foreach ($data as &$row) {
            $this->rows[] = $this->_prepArgs($row);
        }
    }

    /**
     * Compile Template
     */
    protected function _compileTemplate()
    {
        if ($this->template === null) {
            $this->template = $this->_defaultTemplate();

            return;
        }

        foreach ($this->_defaultTemplate() as $field => $template) {
            if (! isset($this->template[$field])) {
                $this->template[$field] = $template;
            }
        }
    }

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
}
