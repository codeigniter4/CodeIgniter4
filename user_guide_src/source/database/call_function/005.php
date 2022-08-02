<?php

    $stringArray = [' A simple string ', new RawSql('CURRENT_TIMESTAMP()'), false, null];

    $escapedString = $db->escape($stringArray);

    $this->assertSame("'A simple string'", $escapedString[0]); // adds quotes
    $this->assertSame('CURRENT_TIMESTAMP()', $escapedString[1]); // does not add quotes
    $this->assertSame(0, $escapedString[2]); // converts bool to 1 or 0
    $this->assertSame('NULL', $escapedString[3]); // null returns NULL without quotes
    $this->assertSame("'{braces}'", $escapedString[4]); // actual braces
