<?php

$page      = $request->input()->get()->integer('page', 1);
$remember  = $request->input()->post()->boolean('remember', false);
$name      = $request->input()->json()->string('name');
$published = $request->input()->raw()->boolean('published', false);
