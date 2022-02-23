<?php

$data = [
    'blog_title'   => 'My Blog Title',
    'blog_heading' => 'My Blog Heading',
];

echo $parser->setData($data)
            ->render('blog_template');
