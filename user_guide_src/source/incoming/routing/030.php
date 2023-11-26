<?php

// The route is defined as:
$routes->get('users/(:num)/gallery/(:num)', 'Galleries::showUserGallery/$1/$2', ['as' => 'user_gallery']);

?>

<!-- Generate the URI to link to user ID 15, gallery 12: -->
<a href="<?= url_to('user_gallery', 15, 12) ?>">View Gallery</a>
<!-- Result: 'http://example.com/users/15/gallery/12' -->
