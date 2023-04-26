<?php

// Redirect to a URI path relative to baseURL with status code 301.
return redirect()->to('admin/home', 301);

// Redirect to a route with status code 308.
return redirect()->route('user_gallery', [], 308);

// Redirect back with status code 302.
return redirect()->back(302);
