<?php

try {
    $user = $userModel->find($id);
} catch (\Exception $e) {
    die($e->getMessage());
}
