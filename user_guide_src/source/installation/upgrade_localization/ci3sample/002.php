<?php

// error.php
$lang['error_email_missing']    = 'You must submit an email address';
$lang['error_url_missing']      = 'You must submit a URL';
$lang['error_username_missing'] = 'You must submit a username';

// ...

$this->lang->load('error', $lang);
echo $this->lang->line('error_email_missing');
