<?php

$builder->having(['title =' => 'My Title', 'id <' => $id]);
// Produces: HAVING title = 'My Title', id < 45
