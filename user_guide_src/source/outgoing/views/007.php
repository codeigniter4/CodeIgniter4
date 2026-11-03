<?php

// Cache the view for 60 seconds
return view('file_name', $data, ['cache' => 60, 'cache_name' => 'my_cached_view']);
