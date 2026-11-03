<?php

// Checks that "Hello World" does NOT exist on the page
if ($results->dontSee('Hello World')) {
    // ...
}

// Checks that "Hellow World" does NOT exist within any h1 tag
if ($results->dontSee('Hello World', 'h1')) {
    // ...
}
