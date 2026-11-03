<?php

$uri = new \CodeIgniter\HTTP\URI('http://www.example.com/some/path');
echo $uri->getScheme(); // 'http'

$uri = $uri->withScheme('https');
echo $uri->getScheme(); // 'https'
