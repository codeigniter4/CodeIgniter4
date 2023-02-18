<?php

// The route is defined as:
$routes->get('users/(:num)/gallery/(:num)', 'Galleries::showUserGallery/$1/$2');

?>

<?php

// Generate the route with user ID 15, gallery 12:
route_to('Galleries::showUserGallery', 15, 12);
// Result: '/users/15/gallery/12'
