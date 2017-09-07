<?php
error_reporting(E_ALL);
require_once 'helper.php';
$res = '';
$get_setting = readJSON('settingjson.cfg');

if (isset($_POST['save'])) {

    $target = $_POST['target'];

    $string = '{
"target": "' . $target . '",
"copyassets": "0"
}';

    $hasil_setting = createFile($string, 'settingjson.cfg');
    $res = '<p>Setting Updated</p>';
}
?>
<!doctype html>
<html>
    <head>
        <title>Harviacode Codeigniter CRUD Generator</title>
        <link rel="stylesheet" href="bootstrap.min.css"/>
        <style>
            body{
                padding: 15px;
            }
        </style>
    </head>
    <body>
        <div class="row">
            <div class="col-md-3">
                <?php echo $res; ?>
                <form action="setting.php" method="POST">

                    <div class="form-group">
                        <label>Target Folder</label>
                        <div class="row">
                            <?php $target = $_POST['target'] ? $_POST['target'] : $get_setting->target; ?>
                            <div class="col-md-6">
                                <div class="radio" style="margin-bottom: 0px; margin-top: 0px">
                                    <label>
                                        <input type="radio" name="target" value="../application/" <?php echo $target == '../application/' ? 'checked' : ''; ?>>
                                        ../application/
                                    </label>
                                </div>                            
                            </div>
                            <div class="col-md-6">
                                <div class="radio" style="margin-bottom: 0px; margin-top: 0px">
                                    <label>
                                        <input type="radio" name="target" value="output/" <?php echo $target == 'output/' ? 'checked' : ''; ?>>
                                        output/
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <input type="submit" value="Save" name="save" class="btn btn-primary" />
                    <a href="../index.php" class="btn btn-default">Back</a>
                </form>
            </div>
            <div class="col-md-4">

            </div>
        </div>
    </body>
</html>

