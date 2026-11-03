<?php

$type = $request->negotiate('encoding', ['gzip']);
// or
$type = $negotiate->encoding(['gzip']);
