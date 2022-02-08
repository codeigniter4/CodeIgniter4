<?php

$string = '<p>Here is a paragraph & an entity (&#123;).</p>';
$string = xml_convert($string);
echo $string;
