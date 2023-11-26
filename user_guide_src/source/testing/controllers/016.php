<?php

// Make sure API calls do not try to use the Debug Toolbar
$this->assertNotFilter('api/v1/widgets', 'after', 'toolbar');
