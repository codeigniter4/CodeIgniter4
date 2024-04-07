<?php

// Check that h1 element which contains class "heading" does NOT exist on the page
if ($results->dontSeeXPath('//h1[contains(@class, "heading")]')) {
    // ...
}

// Check that h1 element which contains class "heading" and text "Hello World" does NOT exist on the page
if ($results->dontSeeXPath('//h1[contains(@class, "heading")][contains(.,"Hello world")]')) {
    // ...
}
