<?php

$uri = new \CodeIgniter\HTTP\URI('http://www.example.com/some/path');

echo $uri->getPath();                            // '/some/path'
echo $uri->setPath('/another/path')->getPath();  // '/another/path'
