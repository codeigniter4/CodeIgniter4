<?php

// Verify that a link exists with 'Upgrade Account' as the text::
$results->assertSeeLink('Upgrade Account');

// Verify that a link exists with 'Upgrade Account' as the text, AND a class of 'upsell'
$results->assertSeeLink('Upgrade Account', '.upsell');
