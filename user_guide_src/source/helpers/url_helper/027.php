<?php

// Outputs "blog"
echo parse_subdomain('blog.example.com');

// Outputs an empty string
echo parse_subdomain('example.com');
echo parse_subdomain('example.co.uk');

// Outputs "shop" - correctly handles two-part TLDs
echo parse_subdomain('shop.example.co.uk');

// Outputs "shop.old"
echo parse_subdomain('shop.old.example.co.uk');
