<?php

$builder->whereDate('created_at', '2026-01-31');
$builder->whereYear('created_at', 2026);
$builder->whereMonth('created_at', 1);
$builder->whereDay('created_at', 31);

// You can include a comparison operator at the end of the field name.
$builder->whereDate('created_at >=', '2026-01-01');
