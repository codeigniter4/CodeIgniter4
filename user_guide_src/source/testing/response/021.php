<?php

// Check that a link exists with 'Upgrade Account' as the text::
$results->seeLink('Upgrade Account');
// Check that a link exists with 'Upgrade Account' as the text, AND a class of 'upsell'
$results->seeLink('Upgrade Account', '.upsell');
