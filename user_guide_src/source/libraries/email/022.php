<?php

$filename = '/img/photo1.jpg';
$email->attach($filename);

foreach ($list as $address) {
    $email->setTo($address);
    $cid = $email->setAttachmentCID($filename);
    $email->setMessage('<img src="cid:' . $cid . '" alt="photo1">');
    $email->send();
}
