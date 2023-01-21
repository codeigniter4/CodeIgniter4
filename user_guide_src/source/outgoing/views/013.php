<?php

use App\Controllers\Blog;

$routes->get('blog', [Blog::class, 'index']);
