<?php

try {
    directory_mirror($uploadedImages, FCPATH . 'images/');
} catch (Throwable $e) {
    echo 'Failed to export uploads!';
}
