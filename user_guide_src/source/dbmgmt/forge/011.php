<?php

$forge->addPrimaryKey('blog_id', 'pd_name');
// gives PRIMARY KEY `pd_name` (`blog_id`)

$forge->addUniqueKey(['blog_id', 'uri'], 'key_name');
// gives UNIQUE KEY `key_name` (`blog_id`, `uri`)
