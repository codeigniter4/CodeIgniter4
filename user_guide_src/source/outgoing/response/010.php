<?php

$options = [
    'max-age'  => 300,
    's-maxage' => 900,
    'etag'     => 'abcde',
];
$this->response->setCache($options);
