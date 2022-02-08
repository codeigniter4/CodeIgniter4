<?php

public filters = [
    'foo' => ['before' => ['admin/*'], 'after' => ['users/*']],
    'bar' => ['before' => ['api/*', 'admin/*']],
];
