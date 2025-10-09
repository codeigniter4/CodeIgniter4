<?php

use App\Enums\UserStatus;

$user = $userModel->find(1);

// Returns a UserStatus enum instance
echo $user->status->value; // 'active'

// Set using enum
$user->status = UserStatus::Inactive;

// Or set using the backing value (will be converted to enum on read)
$user->status = 'pending';

// Note: Internally, enums are always stored as their backing value (string/int)
// in the entity's $attributes array
