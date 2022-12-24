<?php

echo $request->getMethod(true);  // Outputs: POST
echo $request->getMethod(false); // Outputs: post
echo $request->getMethod();      // Outputs: post
