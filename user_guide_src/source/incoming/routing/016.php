<?php

$routes->get('feed', function () {
    $rss = new RSSFeeder();

    return $rss->feed('general');
});
