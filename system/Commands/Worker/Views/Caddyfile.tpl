# CodeIgniter 4 - FrankenPHP Worker Mode Configuration
#
# This Caddyfile configures FrankenPHP to run CodeIgniter in worker mode.
# Adjust settings based on your server resources and application needs.
#
# Start with: frankenphp run

{
    # FrankenPHP worker configuration
    frankenphp {
        # Worker configuration
        worker {
            # Path to the worker file
            file public/frankenphp-worker.php

            # Number of workers (default: 2x CPU cores)
            # Adjust based on your server capacity
            # num 16

            # Watch for PHP code changes (development only)
            watch app/**/*.php
            watch vendor/**/*.php
            watch .env
        }
    }

    # Disable admin API (recommended for production)
    admin off
}

# HTTP server configuration
:8080 {
    # Document root
    root * public

    # Enable compression
    encode zstd br gzip

    # Route all PHP requests through the worker
    php_server {
        # Route all requests through the worker
        try_files {path} frankenphp-worker.php
    }

    # Serve static files
    file_server
}
