<?php

$response->setCookie('foo', 'bar');

ob_start();
$this->response->send();
$output = ob_get_clean(); // in case you want to check the actual body

$this->assertHeaderEmitted('Set-Cookie: foo=bar');
