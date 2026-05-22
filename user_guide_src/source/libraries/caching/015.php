<?php

// Simple dynamic TTL
$cache->remember('key', static fn ($value) => 60, static fn () => fetchData());

// Value-aware TTL
$cache->remember(
    'key',
    static fn ($value) => $value->expires_at - time(),
    static fn () => fetchData(),
);
