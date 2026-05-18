<?php

$page     = $request->getQueryInput()->integer('page', 1);
$remember = $request->getPostInput()->boolean('remember', false);
$name     = $request->getPayloadInput()->string('name');
