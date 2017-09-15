<h2>Create A <?= $single_name ?></h2>

@= form_open(); ?>

<?= $uikit->row([], function() use($uikit, $fields) {

    $sizes = [
        's' => 12,
        'm' => 6,
        'l' => 4
    ];
    echo $uikit->column(['sizes' => $sizes], function() use($uikit, $fields) {

        foreach ($fields as $field)
        {
            echo $uikit->inputWrap( humanize($field['name']), null, function() use($uikit, $field) {

                switch ($field['type'])
                {
                    case 'text':
                        echo "            <input type='text' class='form-control' name='{$field['name']}' />\n";
                        break;
                    case 'number':
                        echo "            <input type='number' class='form-control' name='{$field['name']}' />\n";
                        break;
                    case 'date':
                        echo "            <input type='date' class='form-control' name='{$field['name']}' />\n";
                        break;
                    case 'datetime':
                        echo "            <input type='datetime' class='form-control' name='{$field['name']}' />\n";
                        break;
                    case 'time':
                        echo "            <input type='time' class='form-control' name='{$field['name']}' />\n";
                        break;
                    case 'textarea':
                        echo "            <textarea name='{$field['name']}' class='form-control'></textarea>\n";
                        break;
                }

            } );
        }

    });

}); ?>


    <input type="submit" name="submit" value="Create <?= $single_name ?>" class="btn btn-primary" />
    &nbsp;or&nbsp;
    <a href="@= site_url('<?= $lower_name ?>') ?>">Cancel</a>

@= form_close(); ?>
