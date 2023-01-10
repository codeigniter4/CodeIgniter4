<?php

$this->forge->addKey(['category', 'name'], false, false, 'category_name');
$this->forge->addPrimaryKey('id', 'pk_actions');
$this->forge->addForeignKey('userid', 'user', 'id', '', '', 'userid_fk');
$this->forge->processIndexes('actions');
/* gives:
ALTER TABLE `actions` ADD KEY `category_name` (`category`, `name`);
ALTER TABLE `actions` ADD CONSTRAINT `pk_actions` PRIMARY KEY(`id`);
ALTER TABLE `actions` ADD CONSTRAINT `userid_fk` FOREIGN KEY (`userid`) REFERENCES `user`(`id`);
*/
