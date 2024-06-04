<?php

$client = service('curlrequest'); // Since v4.5.0, this code is recommended due to performance improvements

// The code above is the same as the code below.
$client = \Config\Services::curlrequest();
