<?php

echo $pager->only(['search', 'order'])->links();
// Page 3 link: https://domain.tld?search=foo&order=asc&page=3
