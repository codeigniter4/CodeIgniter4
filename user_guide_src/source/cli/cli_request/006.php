<?php

// php index.php user 21 --foo bar -f
echo $request->getOptionString();     // -foo bar -f
echo $request->getOptionString(true); // --foo bar -f
