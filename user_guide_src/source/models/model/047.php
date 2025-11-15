<?php

$users = $userModel->asArray()->where('status', 'active')->findAll();
