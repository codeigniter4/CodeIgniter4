<br><br><br>

<?php
\Config\Services::validation()->listErrors();

helper('form');
echo form_open('form/process',['enctype' => 'multipart/form-data']);

echo form_input('avatar', '', '', 'file');

echo form_submit('Send', 'Send');
echo form_close();