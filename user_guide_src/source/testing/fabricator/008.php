<?php

$fabricator = new Fabricator(UserFabricator::class);
$testUser   = $fabricator->make();
print_r($testUser);
