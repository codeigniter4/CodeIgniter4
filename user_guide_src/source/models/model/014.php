<?php

$deletedUsers = $userModel->onlyDeleted()->findAll();
