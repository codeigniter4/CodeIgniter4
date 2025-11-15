<?php

$response = $client->request('PUT', '/put', ['json' => ['foo' => 'bar']]);
