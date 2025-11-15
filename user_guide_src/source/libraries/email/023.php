<?php

// You need to pass false while sending in order for the email data
// to not be cleared - if that happens, printDebugger() would have
// nothing to output.
$email->send(false);

// Will only print the email headers, excluding the message subject and body
$email->printDebugger(['headers']);
