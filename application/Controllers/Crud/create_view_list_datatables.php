<?php 

$string = "<div class=\"row bg-light lter\">
    <div class=\"col-md-4 \"><h4>".ucfirst($table_name)."</h4></div>
    <div class=\"col-md-8 text-right\">
\t<?php echo anchor(site_url('".$v_url."/create'),'Create', 'class=\"btn btn-primary\"'); ?>";
if ($export_excel == '1') {
    $string .= "\n\t<?php echo anchor(site_url('".$v_url."/excel'), 'Excel', 'class=\"btn btn-primary\"'); ?>";
}
if ($export_word == '1') {
    $string .= "\n\t<?php echo anchor(site_url('".$v_url."/word'), 'Word', 'class=\"btn btn-primary\"'); ?>";
}
if ($export_pdf == '1') {
    $string .= "\n\t<?php echo anchor(site_url('".$v_url."/pdf'), 'PDF', 'class=\"btn btn-primary\"'); ?>";
}
                    
$string.="\n    </div>
</div>
    <table class=\"table table-responsive m-b-none text-sm display nowrap datatables\" width=\"100%\" >
        <thead>
            <th>No</th>";
foreach ($non_pk as $row) {
    $string .= "\n\t    <th>" . label($row['column_name']) . "</th>";
}
$string .= "\n\t    <th>Action</th>
        </thead>
        <tbody>";
$string .= "<?php foreach ($" . $c_url . "_data as \$$c_url) { ?>
                <tr>";

$string .= "\n\t\t\t<td width=\"80px\"></td>";
foreach ($non_pk as $row) {
    $string .= "\n\t\t\t<td><?php echo $" . $c_url ."->". $row['column_name'] . " ?></td>";
}


$string .= "\n\t\t\t<td style=\"text-align:center\" width=\"200px\">"
        . "\n\t\t\t\t<?php "
        . "\n\t\t\t\t    echo anchor(site_url('".$v_url."/view/'.$".$c_url."->".$pk."),'View'); "
        . "\n\t\t\t\t    echo ' | '; "
        . "\n\t\t\t\t    echo anchor(site_url('".$v_url."/edit/'.$".$c_url."->".$pk."),'Edit'); "
        . "\n\t\t\t\t    echo ' | '; "
        . "\n\t\t\t\t    echo anchor(site_url('".$v_url."/delete/'.$".$c_url."->".$pk."),'Delete','onclick=\"javasciprt: return confirm(\\'Are You Sure ?\\')\"'); "
        . "\n\t\t\t\t?>"
        . "\n\t\t\t</td>";

$string .=  "\n\t\t</tr>
                <?php } ?>
        </tbody>
    </table>";


$hasil_view_list = createFile($string, $target."views/" . $v_url . "/" . $v_list_file);
