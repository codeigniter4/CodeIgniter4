<?php

$builder->like('title', 'match', 'before'); // Produces: WHERE `title` LIKE '%match' ESCAPE '!'
$builder->like('title', 'match', 'after');  // Produces: WHERE `title` LIKE 'match%' ESCAPE '!'
$builder->like('title', 'match', 'both');   // Produces: WHERE `title` LIKE '%match%' ESCAPE '!'
