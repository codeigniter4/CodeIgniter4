<?php

$format = $request->negotiate('media', $supported, true);
// or
$format = $negotiate->media($supported, true);
