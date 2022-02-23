<?php

echo $response->getStatusCode();
echo $response->getBody();
echo $response->getHeader('Content-Type');
$language = $response->negotiateLanguage(['en', 'fr']);
