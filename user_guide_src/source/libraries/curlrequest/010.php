<?php

// Get a header line
echo $response->getHeaderLine('Content-Type');

// Get all headers
foreach ($response->getHeaders() as $name => $value) {
    echo $name . ': ' . $response->getHeaderLine($name) . "\n";
}
