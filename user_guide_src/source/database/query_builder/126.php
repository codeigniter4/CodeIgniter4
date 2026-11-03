<?php

$builder->whereBetween('created_at', ['2026-01-01', '2026-01-31']);

// Produces:
// WHERE created_at BETWEEN '2026-01-01' AND '2026-01-31'
