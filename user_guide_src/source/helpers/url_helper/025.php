<?php

// The route is defined as:
$routes->add(
    '{locale}/users/(:num)/gallery/(:num)',
    'Galleries::showUserGallery/$1/$2',
    ['as' => 'user_gallery']
);

?>

<a href="<?= url_to('user_gallery', 15, 12, 'en') ?>">View Gallery</a>
<!-- Result: 'http://example.com/en/users/15/gallery/12' -->
