<?php

$this->validate($request, [
    'foo' => 'required|even',
]);
