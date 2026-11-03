<?php

// Get with default value
$apiKey = $context->getHidden('api_key', 'default_key');

// Get only specific hidden keys
$credentials = $context->getOnlyHidden(['api_key', 'api_secret']);

// Get all hidden data except specific keys
$data = $context->getExceptHidden(['db_password']);

// Get all hidden data
$allHidden = $context->getAllHidden();
