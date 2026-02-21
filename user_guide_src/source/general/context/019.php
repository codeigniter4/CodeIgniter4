<?php

if ($context->hasHidden('api_key')) {
    // API key is set
}

if ($context->missingHidden('api_key')) {
    // API key is not set
}
