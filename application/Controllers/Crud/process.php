<?php

$hasil = array();

$table_name = safe($_POST['table_name']);
$jenis_tabel = safe($_POST['jenis_tabel']);
$export_excel = safe($_POST['export_excel']);
$export_word = safe($_POST['export_word']);
$export_pdf = safe($_POST['export_pdf']);
$controller = safe($_POST['controller']);
$model = safe($_POST['model']);

if ($table_name <> '') {
    // set data
    $table_name = $table_name;
    $c = $controller <> '' ? ucfirst($controller) : ucfirst($table_name);
    $m = $model <> '' ? ucfirst($model) : ucfirst($table_name) . '_model';
    $v_list = $table_name . "_list";
    $v_read = $table_name . "_read";
    $v_form = $table_name . "_form";
    $v_doc = $table_name . "_doc";
    $v_pdf = $table_name . "_pdf";

    // url
    $c_url = strtolower($c);

    // filename
    $c_file = $c . '.php';
    $m_file = $m . '.php';
    $v_list_file = $v_list . '.php';
    $v_read_file = $v_read . '.php';
    $v_form_file = $v_form . '.php';
    $v_doc_file = $v_doc . '.php';
    $v_pdf_file = $v_pdf . '.php';

    // read setting
    $get_setting = readJSON('settingjson.cfg');
    $target = $get_setting->target;

    $view_subfolder = $get_setting->view_subfolder;
    $controller_subfolder = $get_setting->controller_subfolder;
    $v_url = $c_url;
    if ($view_subfolder != '') {
        if (!file_exists($target . "views/" . $view_subfolder)) {
            mkdir($target . "views/" . $view_subfolder, 0777, true);
        }
        $v_url = $view_subfolder . '/' . $c_url;
    }

    if (!file_exists($target . "views/" . $v_url)) {
        mkdir($target . "views/" . $v_url, 0777, true);
    }

    if ($controller_subfolder != '') {
        if (!file_exists($target . "controllers/" . $controller_subfolder)) {
            mkdir($target . "controllers/" . $controller_subfolder, 0777, true);
        }
        $c_file = $controller_subfolder . '/' . $c_file;
    }

    $pk = $hc->primary_field($table_name);
    $non_pk = $hc->not_primary_field($table_name);
    $all = $hc->all_field($table_name);

    // generate
    include 'create_config_pagination.php';
    include 'create_controller.php';
    include 'create_model.php';
    if ($jenis_tabel == 'regular_table') {
        include 'create_view_list.php';
    }
    if ($jenis_tabel == 'datatables_local') {
        include 'create_view_list_datatables.php';
    }
    if ($jenis_tabel == 'datatables_server') {
        include 'create_view_list_datatables_server.php';
    }

    include 'create_view_form.php';
    include 'create_view_read.php';

    $export_excel == 1 ? include 'create_exportexcel_helper.php' : '';
    $export_word == 1 ? include 'create_view_list_doc.php' : '';
    $export_pdf == 1 ? include 'create_pdf_library.php' : '';
    $export_pdf == 1 ? include '/create_view_list_pdf.php' : '';

    $hasil[] = $hasil_controller;
    $hasil[] = $hasil_model;
    $hasil[] = $hasil_view_list;
    $hasil[] = $hasil_view_form;
    $hasil[] = $hasil_view_read;
    $hasil[] = $hasil_view_doc;
    $hasil[] = $hasil_view_pdf;
    $hasil[] = $hasil_config_pagination;
    $hasil[] = $hasil_exportexcel;
    $hasil[] = $hasil_pdf;
} else {
    $hasil[] = 'No table selected.';
}
