<?php

$forge->addKey('blog_id', true);
// gives PRIMARY KEY `blog_id` (`blog_id`)

$forge->addKey('blog_id', true);
$forge->addKey('site_id', true);
// gives PRIMARY KEY `blog_id_site_id` (`blog_id`, `site_id`)

$forge->addKey('blog_name');
// gives KEY `blog_name` (`blog_name`)

$forge->addKey(['blog_name', 'blog_label'], false, false, 'my_key_name');
// gives KEY `my_key_name` (`blog_name`, `blog_label`)

$forge->addKey(['blog_id', 'uri'], false, true, 'my_key_name');
// gives UNIQUE KEY `my_key_name` (`blog_id`, `uri`)
