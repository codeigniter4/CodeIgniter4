<?php

$users = $userModel->paginate(10, 'group1', null, $segment);
