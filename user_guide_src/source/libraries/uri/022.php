<?php

$uri = new \CodeIgniter\HTTP\URI('http://www.example.com/some/path#first-heading');

echo $uri->getFragment();   // 'first-heading'
echo $uri->setFragment('second-heading')->getFragment();    // 'second-heading'
