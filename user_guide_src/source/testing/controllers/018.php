<?php

// Make sure no filters run for our static pages
$this->assertNotHasFilters('about/contact', 'before');
