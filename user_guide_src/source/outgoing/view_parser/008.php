<?php

$data = [
    'blog_title'   => 'My Blog Title',
    'blog_heading' => 'My Blog Heading',
    'blog_entries' => [
        [
            'title' => 'Title 1',
            'body'  => 'Body 1',
        ],
    ],
];

return $parser->setData($data)->render('blog_template');
