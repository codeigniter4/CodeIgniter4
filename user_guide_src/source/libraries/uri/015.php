<?php

$uri = new \CodeIgniter\HTTP\URI('ftp://user:password@example.com:21/some/path');

echo $uri->getPort();   // 21
echo $uri->setPort(2201)->getPort(); // 2201
