<?php

$builder->whereColumn('created_at', 'updated_at');
// Produces: WHERE created_at = updated_at

$builder->whereColumn('updated_at >', 'created_at');
// Produces: WHERE updated_at > created_at

$builder->whereColumn('updated_at !=', 'created_at');
// Produces: WHERE updated_at != created_at
