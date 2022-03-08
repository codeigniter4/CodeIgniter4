<?php

$builder->having('user_id', 45); // Produces: HAVING `user_id` = 45 in some databases such as MySQL
$builder->having('user_id', 45, false); // Produces: HAVING user_id = 45
