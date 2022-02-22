<?php

$newName = $file->getRandomName();
$file->move(WRITEPATH . 'uploads', $newName);
