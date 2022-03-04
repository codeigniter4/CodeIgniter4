<?php

try {
    $user = $userModel->find($id);
} catch (\CodeIgniter\UnknownFileException $e) {
    // do something here...
}
