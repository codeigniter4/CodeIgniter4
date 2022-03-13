<?php

$routes->get('feed', static function () {
    $rss = new RSSFeeder();

    return $rss->feed('general');
});
