<?php

// Accept-Encoding: gzip, deflate, sdch
echo 'Accept-Encoding: ' . $request->getHeaderLine('accept-encoding');
