<?php

$string = "<div class=\"row bg-light lter\">
    <div class=\"col-md-4 \"><h4>" . ucfirst($table_name) . "</h4></div>
    <div class=\"col-md-8 text-right\">
        <?php echo anchor(site_url('" . $v_url . "/create'),'Create', 'class=\"btn btn-primary\"'); ?>";
if ($export_excel == '1') {
    $string .= "\n\t<?php echo anchor(site_url('" . $v_url . "/excel'), 'Excel', 'class=\"btn btn-primary\"'); ?>";
}
if ($export_word == '1') {
    $string .= "\n\t<?php echo anchor(site_url('" . $v_url . "/word'), 'Word', 'class=\"btn btn-primary\"'); ?>";
}
if ($export_pdf == '1') {
    $string .= "\n\t<?php echo anchor(site_url('" . $v_url . "/pdf'), 'PDF', 'class=\"btn btn-primary\"'); ?>";
}
$string .= "\n    </div>
</div>
    <table class=\"table table-responsive m-b-none text-sm display nowrap\" id=\"mytable\">
        <thead>
            <tr>
                <th width=\"80px\">No</th>";
foreach ($non_pk as $row) {
    $string .= "\n\t\t<th>" . label($row['column_name']) . "</th>";
}
$string .= "\n\t\t<th width=\"200px\">Action</th>
            </tr>
        </thead>";

$column_non_pk = array();
foreach ($non_pk as $row) {
    $column_non_pk[] .= "{\"data\": \"" . $row['column_name'] . "\"}";
}
$col_non_pk = implode(",\n\t\t", $column_non_pk);

$string .= "\n    </table>
<script type=\"text/javascript\">
    $(document).ready(function() {

        var t = $(\"#mytable\").dataTable({
            oLanguage: {
                sProcessing: \"loading...\"
            },
            processing: true,
            serverSide: true,
            ajax: {\"url\": \"<?php echo site_url(\"" . $v_url . "/json\"); ?>\", \"type\": \"POST\"},
            columns: [
                {
                    \"data\": \"$pk\",
                    \"orderable\": false,
                    \"searchable\": false
                },\n\t\t" . $col_non_pk . ",
                {
                    \"data\" : \"action\",
                    \"orderable\": false,
                    \"className\" : \"text-center\"
                }
            ],
            order: [[0, 'desc']],
        });
    });
</script>";


$hasil_view_list = createFile($string, $target . "views/" . $v_url . "/" . $v_list_file);
