<?php
    $column_all = array();
    foreach ($all as $row) {
        $column_all[] = $row['column_name'];
    }
    $columnall = implode(',', $column_all);


$string = "<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class " . $m . " extends CI_Model {

    protected \$table = '$table_name';
    protected \$primaryKey = '$pk';
    protected \$allowedFields = $column_all;
    protected \$returnType = 'array';
    protected \$useSoftDeletes = false;
    protected \$useTimestamps = false;
    protected \$validationRules = [];
    protected \$validationMessages = [];
    protected \$skipValidation = false;


    function __construct() {
        parent::__construct();
    }";

if ($jenis_tabel == 'datatables_server') {

    $string .="\n\n    // datatables
    function json() {
        \$this->datatables->select('" . $columnall . "');
        \$this->datatables->from('" . $table_name . "');
        //add this line for join
        //\$this->datatables->join('table2', '" . $table_name . ".field = table2.field');
        \$this->datatables->add_column('action', anchor(site_url('" . $v_url . "/view/\$1'),'View').\" | \".anchor(site_url('" . $v_url . "/edit/\$1'),'Edit').\" | \".anchor(site_url('" . $v_url . "/delete/\$1'),'Delete','onclick=\"javasciprt: return confirm(\\'Are You Sure ?\\')\"'), '$pk');
        return \$this->datatables->generate();
    }";
}

$string .="\n\n    // get total rows
    function total_rows() {
        \$this->db->table(\$this->table);
        return \$builder->countAll();
    }

function total_rows() { ";
$string .= "return \$this->db->count_all_results(\$this->table);
    }

    // get data with limit and search
    function get_limit_data(\$limit, \$start = 0, \$q = NULL) {
        \$this->db->order_by(\$this->id, \$this->order);
    
}";

$hasil_model = createFile($string, $target . "models/" . $m_file);
