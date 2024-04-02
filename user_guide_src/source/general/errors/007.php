<?php

use CodeIgniter\Exceptions\PageNotFoundException;

$page = $pageModel->find($id);

if ($page === null) {
    throw PageNotFoundException::forPageNotFound();
}
