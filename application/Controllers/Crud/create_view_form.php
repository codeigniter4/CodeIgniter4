<?php 

$string = "<div><h4>".ucfirst($table_name)." </h4></div>
<form action=\"<?php echo \$action; ?>\" method=\"post\">";
foreach ($non_pk as $row) {
    if ($row["data_type"] == 'text')
    {
    $string .= "\n    <div class=\"form-group\">
        <label for=\"".$row["column_name"]."\">".label($row["column_name"])." <?php echo form_error('".$row["column_name"]."') ?></label>
        <textarea class=\"form-control\" rows=\"3\" name=\"".$row["column_name"]."\" id=\"".$row["column_name"]."\" placeholder=\"".label($row["column_name"])."\"><?php echo $".$row["column_name"]."; ?></textarea>
    </div>";
    } else
    {
    $string .= "\n    <div class=\"form-group\">
        <label for=\"".$row["data_type"]."\">".label($row["column_name"])." <?php echo form_error('".$row["column_name"]."') ?></label>
        <input type=\"text\" class=\"form-control\" name=\"".$row["column_name"]."\" id=\"".$row["column_name"]."\" placeholder=\"".label($row["column_name"])."\" value=\"<?php echo $".$row["column_name"]."; ?>\" />
    </div>";
    }
}
$string .= "\n    <footer class=\"panel-footer text-right bg-light lter\">";
$string .= "\n        <?php if (!empty($".$pk.")) { ?>";
$string .= "\n            <input type=\"hidden\" name=\"".$pk."\" value=\"<?php echo $".$pk."; ?>\" /> ";
$string .= "\n            <a href=\"<?php echo site_url('".$v_url."/delete/.$pk'); ?>\" class=\"confirm btn btn-danger btn-s-xs\">Delete</a>";
$string .= "\n        <?php } ?>";            
$string .= "\n        <button type=\"submit\" class=\"btn btn-primary\"><?php echo \$button ?></button> ";
$string .= "\n        <a href=\"<?php echo site_url('".$v_url."') ?>\" class=\"btn btn-warning\">Cancel</a>";
$string .= "\n    </footer>";
$string .= "\n</form>";

$hasil_view_form = createFile($string, $target."views/" . $v_url . "/" . $v_form_file);
