<?php

// Return as standard objects
$users = $userModel->asObject()->where('status', 'active')->findAll();

// Return as custom class instances
$users = $userModel->asObject('User')->where('status', 'active')->findAll();
