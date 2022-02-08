<?php

$forge->addPrimaryKey('blog_id');
// gives PRIMARY KEY `blog_id` (`blog_id`)

$forge->addUniqueKey(['blog_id', 'uri']);
// gives UNIQUE KEY `blog_id_uri` (`blog_id`, `uri`)
