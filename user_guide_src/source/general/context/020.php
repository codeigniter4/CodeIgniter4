<?php

// Remove a single hidden value
$context->removeHidden('api_key');

// Remove multiple hidden values
$context->removeHidden(['api_key', 'api_secret']);
