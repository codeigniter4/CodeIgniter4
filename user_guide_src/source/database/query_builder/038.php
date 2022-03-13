<?php

$builder->like('title', 'match');
// Produces: WHERE `title` LIKE '%match%' ESCAPE '!'
