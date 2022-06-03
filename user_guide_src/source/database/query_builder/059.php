<?php

$builder->havingLike('title', 'match');
// Produces: HAVING `title` LIKE '%match%' ESCAPE '!'
