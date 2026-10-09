<?php

$parser->setData(['role' => $userData['role']]);

echo $parser->renderString('{if $role === "admin"}Admin{endif}');
