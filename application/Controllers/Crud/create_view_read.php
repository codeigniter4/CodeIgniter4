<?php

$string = "<div><h4>".ucfirst($table_name)." </h4></div>
<table class=\"table\">";
foreach ($non_pk as $row) {
    $string .= "\n    <tr><td>" . label($row["column_name"]) . "</td><td><?php echo $" . $row["column_name"] . "; ?></td></tr>";
}
$string .= "\n    <tr><td></td><td><a href=\"<?php echo site_url('" . $v_url . "') ?>\" class=\"btn btn-warning\"\>Cancel</a></td></tr>";
$string .= "\n</table>";

$hasil_view_read = createFile($string, $target . "views/" . $v_url . "/" . $v_read_file);
