<?php

echo $parser->render('blog_template', [
    'cache'      => HOUR,
    'cache_name' => 'something_unique',
]);
