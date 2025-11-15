<?php

// If your feature test contains this:
$result = $this->withBodyFormat('json')->post('users', $userInfo);

// Your controller can then get the parameters passed in with:
$userInfo = $this->request->getJson();
