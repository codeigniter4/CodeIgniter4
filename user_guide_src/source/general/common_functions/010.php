<?php

// The route is defined as:
$routes->get('users/(:num)/gallery/(:num)', 'Galleries::showUserGallery/$1/$2', ['as' => 'user_gallery']);

?>

<?php

// Generate the route with user ID 15, gallery 12:
route_to('user_gallery', 15, 12);
// Result: '/users/15/gallery/12'
