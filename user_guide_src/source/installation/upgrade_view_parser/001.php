<?php

$parser = service('parser');

$data = [
    'blog_title'   => 'My Blog Title',
    'blog_heading' => 'My Blog Heading',
];

return $parser->setData($data)->render('blog_template');
