<?php

$time = Time::parse('5 years ago');

echo $time->getAge(); // 5
echo $time->age;      // 5
