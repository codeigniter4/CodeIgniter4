<?php

$imageProperties = [
    'src'    => 'images/picture.jpg',
    'alt'    => 'Me, demonstrating how to eat 4 slices of pizza at one time',
    'class'  => 'post_images',
    'width'  => '200',
    'height' => '200',
    'title'  => 'That was quite a night',
    'rel'    => 'lightbox',
];

img($imageProperties);
// <img src="http://site.com/index.php/images/picture.jpg" alt="Me, demonstrating how to eat 4 slices of pizza at one time" class="post_images" width="200" height="200" title="That was quite a night" rel="lightbox">
