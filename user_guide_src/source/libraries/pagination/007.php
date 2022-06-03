<?php

$pager = service('pager');
$pager->setPath('path/for/my-group', 'my-group'); // Additionally you could define path for every group.
$pager->makeLinks($page, $perPage, $total, 'template_name', $segment, 'my-group');
