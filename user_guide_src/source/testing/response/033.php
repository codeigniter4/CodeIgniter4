<?php

// Check that h1 element which contains class "heading" is on the page
if ($results->seeXPath('//h1[contains(@class, "heading")]')) {
    // ...
}

// Check that h1 element which contains class "heading" have a text "Hello World"
if ($results->seeXPath('//h1[contains(@class, "heading")][contains(.,"Hello world")]')) {
    // ...
}
