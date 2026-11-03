<?php

$attributes = [
    'id'    => 'address_info',
    'class' => 'address_info',
];

echo form_fieldset('Address Information', $attributes);
echo "<p>fieldset content here</p>\n";
echo form_fieldset_close();

?>

<!-- Produces: -->
<fieldset id="address_info" class="address_info">
    <legend>Address Information</legend>
    <p>form content here</p>
</fieldset>
