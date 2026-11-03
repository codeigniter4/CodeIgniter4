<?php

// Unsafe: $name becomes part of the template source.
echo $parser->renderString('Hello ' . $name);

// Safe: $name is passed as data.
echo $parser->setData(['name' => $name])->renderString('Hello {name}');
