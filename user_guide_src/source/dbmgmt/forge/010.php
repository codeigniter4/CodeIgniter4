<?php

$forge->addKey('blog_id', true);
// gives PRIMARY KEY `blog_id` (`blog_id`)

$forge->addKey('blog_id', true);
$forge->addKey('site_id', true);
// gives PRIMARY KEY `blog_id_site_id` (`blog_id`, `site_id`)

$forge->addKey('blog_name');
// gives KEY `blog_name` (`blog_name`)

$forge->addKey(['blog_name', 'blog_label']);
// gives KEY `blog_name_blog_label` (`blog_name`, `blog_label`)

$forge->addKey(['blog_id', 'uri'], false, true);
// gives UNIQUE KEY `blog_id_uri` (`blog_id`, `uri`)
