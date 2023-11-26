<?php

$this->response->setHeader('Cache-Control', 'no-cache')
    ->appendHeader('Cache-Control', 'must-revalidate');
