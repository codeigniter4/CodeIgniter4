<?php

echo $uri->getAuthority();  // user@example.com:21
echo $uri->showPassword()->getAuthority();   // user:password@example.com:21

// Turn password display off again.
$uri->showPassword(false);
