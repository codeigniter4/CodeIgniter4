<?php

$title = $request->title;  // same as $request->validated()['title'] ?? null
$body  = $request->body;

if (isset($request->note)) {
    // 'note' was validated and has a non-null value
}
