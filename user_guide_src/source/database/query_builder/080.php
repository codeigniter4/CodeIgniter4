<?php

echo $builder->set('title', 'My Title')->getCompiledInsert(false);
// Produces string: INSERT INTO mytable (`title`) VALUES ('My Title')

echo $builder->set('content', 'My Content')->getCompiledInsert();
// Produces string: INSERT INTO mytable (`title`, `content`) VALUES ('My Title', 'My Content')
