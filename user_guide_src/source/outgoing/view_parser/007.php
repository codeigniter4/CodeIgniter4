<?php

$query = $db->query('SELECT * FROM blog');

$data = [
    'blog_title'   => 'My Blog Title',
    'blog_heading' => 'My Blog Heading',
    'blog_entries' => $query->getResultArray(),
];

echo $parser->setData($data)
    ->render('blog_template');
