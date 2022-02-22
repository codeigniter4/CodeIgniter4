<?php

$charset = $request->negotiate('charset', ['utf-8']);
// or
$charset = $negotiate->charset(['utf-8']);
