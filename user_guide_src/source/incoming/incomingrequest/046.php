<?php

$page     = $request->getGetInput()->integer('page', 1);
$remember = $request->getPostInput()->boolean('remember', false);
$name     = $request->getJSONInput()->string('name');
