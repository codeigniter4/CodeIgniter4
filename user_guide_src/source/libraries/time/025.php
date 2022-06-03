<?php

echo Time::now('America/Chicago')->getUtc(); // false
echo Time::now('UTC')->utc;                  // true
