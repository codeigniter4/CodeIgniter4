<?php

$language    = $request->negotiate('language', ['en-US', 'en-GB', 'fr', 'es-mx']);
$imageType   = $request->negotiate('media', ['image/png', 'image/jpg']);
$charset     = $request->negotiate('charset', ['UTF-8', 'UTF-16']);
$contentType = $request->negotiate('media', ['text/html', 'text/xml']);
$encoding    = $request->negotiate('encoding', ['gzip', 'compress']);
