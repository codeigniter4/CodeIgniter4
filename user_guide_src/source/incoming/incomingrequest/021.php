<?php

$uri = $request->getUri();

echo $uri->getScheme();         // http
echo $uri->getAuthority();      // snoopy:password@example.com:88
echo $uri->getUserInfo();       // snoopy:password
echo $uri->getHost();           // example.com
echo $uri->getPort();           // 88
echo $uri->getPath();           // /path/to/page
echo $uri->getRoutePath();      // path/to/page
echo $uri->getQuery();          // foo=bar&bar=baz
print_r($uri->getSegments());   // Array ( [0] => path [1] => to [2] => page )
echo $uri->getSegment(1);       // path
echo $uri->getTotalSegments();  // 3
