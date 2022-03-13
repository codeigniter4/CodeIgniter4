<?php

$builder->like('title', 'match');
$builder->like('body', 'match');
// WHERE `title` LIKE '%match%' ESCAPE '!' AND  `body` LIKE '%match%' ESCAPE '!'
