<?php
error_reporting(E_ALL);
require_once 'harviacode.php';
?>
<!doctype html>
<html>
    <head>
        <title>CRUDGen Codeigniter CRUD Generator</title>
        <link rel="stylesheet" href="bootstrap.min.css"/>
        <script type="text/javascript" src="../assets/js/jquery.min.js" ></script>        
        <style>
            body{
                padding: 15px;
            }
        </style>
    </head>
    <body>
        <div class="row">
            <div class="col-md-8">
                <h3 style="margin-top: 0px">Codeigniter CRUD Generator 1.4 by <a target="_blank" href="http://harviacode.com">harviacode.com</a></h3>
            </div>
            <div class="col-md-4 text-right">
                <a href="setting.php" class="btn btn-default">Setting</a>
            </div>
        </div>            
        <div class="row text-center">
            <div class="col-md-4">Table&nbsp;&nbsp;&nbsp;List View</div>
            <div class="col-md-3">Export</div>
            <div class="col-md-2">Controller</div>
            <div class="col-md-2">Model</div>
            <div class="col-md-1"></div>
        </div>

        <?php
        $table_list = $hc->table_list();
        $table_list_selected = isset($_POST['table_name']) ? $_POST['table_name'] : '';
        foreach ($table_list as $table) {
            ?>
            <div class="row text-center">
                <form action="" class="process" method="POST">
                    <div class="col-md-4">
                        <input type=text value="<?php echo $table['table_name'] ?>" name="table_name"/>
                        <?php $jenis_tabel = isset($_POST['jenis_tabel']) ? $_POST['jenis_tabel'] : 'regular_table'; ?>
    <!--                        <input type="radio" name="jenis_tabel" value="regular_table" <?php echo $jenis_tabel == 'regular_table' ? 'checked' : ''; ?>>
                        Regular Table-->
                        Datatables
                        <input type="radio" name="jenis_tabel" value="datatables_local" <?php echo $jenis_tabel == 'datatables_local' ? 'checked' : ''; ?>>
                        Local
                        <input type="radio" name="jenis_tabel" value="datatables_server" <?php echo $jenis_tabel == 'datatables_server' ? 'checked' : ''; ?>>
                        Serverside

                    </div>
                    <div class="col-md-3">
                        <?php $export_excel = isset($_POST['export_excel']) ? $_POST['export_excel'] : ''; ?>
                        <input type="checkbox" name="export_excel" value="1" <?php echo $export_excel == '1' ? 'checked' : '' ?>>
                        Export Excel
                        <?php $export_word = isset($_POST['export_word']) ? $_POST['export_word'] : ''; ?>
                        <input type="checkbox" name="export_word" value="1" <?php echo $export_word == '1' ? 'checked' : '' ?>>
                        Export Word

                                                    <!-- <?php // echo file_exists('../application/third_party/mpdf/mpdf.php') ? '' : 'disabled';                         ?>">
                        <?php // $export_pdf = isset($_POST['export_pdf']) ? $_POST['export_pdf'] : '';  ?>
                                                          <input type="checkbox" name="export_pdf" value="1" <?php // echo $export_pdf == '1' ? 'checked' : ''                         ?>
                        <?php // echo file_exists('../application/third_party/mpdf/mpdf.php') ? '' : 'disabled';  ?>>
                                                          Export PDF
                        <?php // echo file_exists('../application/third_party/mpdf/mpdf.php') ? '' : '<small class="text-danger">mpdf required, download <a href="http://harviacode.com">here</a></small>';  ?>
                        -->
                    </div>
                    <div class="col-md-2">
                        <input type="text" id="controller" name="controller" value="<?php echo ucfirst($table['table_name']) ?>" />
                    </div>
                    <div class="col-md-2">
                        <input type="text" id="model" name="model" value="<?php echo ucfirst($table['table_name']) . '_model'; ?>" />
                    </div>
                    <div class="col-md-1">
                        <input type="submit" value="Generate" name="generate" class="btn btn-primary" onclick="javascript: return confirm('This will overwrite the existing files. Continue ?')" />                            
                    </div>    
            </div>
        </form> 
    </div>
<?php } ?>
<div id="ajax_results">
    Generating...
</div>
</body>

<script type="text/javascript">
    $(".process").submit(function (e) {
        e.preventDefault();
        var form = this;
        console.log($(form).serialize());
        $.ajax({
            type: "POST",
            url: 'process_ajax.php',
            data: $(form).serialize(),
            success: function (content) {
                $("#ajax_results").html(content);
            },
            dataType: 'html'
        });
    });
</script>
</html>
