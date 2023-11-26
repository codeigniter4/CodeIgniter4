<?php

$builder->having('user_id = 45'); // Produces: HAVING user_id = 45
$builder->having('user_id', 45); // Produces: HAVING user_id = 45
