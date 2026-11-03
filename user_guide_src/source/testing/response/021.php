<?php

// Check that a link exists with 'Upgrade Account' as the text::
if ($results->seeLink('Upgrade Account')) {
    // ...
}

// Check that a link exists with 'Upgrade Account' as the text, AND a class of 'upsell'
if ($results->seeLink('Upgrade Account', '.upsell')) {
    // ...
}
