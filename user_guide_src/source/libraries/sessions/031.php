<?php

$tempdata = ['newuser' => true, 'message' => 'Thanks for joining!'];
$session->setTempdata($tempdata, null, $expire);
