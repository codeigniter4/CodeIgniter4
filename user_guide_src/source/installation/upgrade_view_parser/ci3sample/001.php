<?php

$this->load->library('parser');

$data = array(
    'blog_title'   => 'My Blog Title',
    'blog_heading' => 'My Blog Heading'
);

$this->parser
    ->parse('blog_template', $data);
