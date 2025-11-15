<?php

use App\Libraries\RSSFeeder;

$routes->get('feed', static function () {
    $rss = new RSSFeeder();

    return $rss->feed('general');
});
