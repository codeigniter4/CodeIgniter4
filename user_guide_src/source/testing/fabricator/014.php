<?php

$user = $fabricator(null, true);

$this->assertIsNumeric($user->id);
$this->dontSeeInDatabase('user', ['id' => $user->id]);
