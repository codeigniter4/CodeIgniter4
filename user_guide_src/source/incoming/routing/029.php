<?php

// The route is defined as:
$routes->get('users/(:num)/gallery(:any)', 'Galleries::showUserGallery/$1/$2');

?>

<!-- Generate the relative URL to link to user ID 15, gallery 12: -->
<a href="<?= route_to('Galleries::showUserGallery', 15, 12) ?>">View Gallery</a>
<!-- Result: '/users/15/gallery/12' -->
