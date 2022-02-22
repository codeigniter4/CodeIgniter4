<?php

if (! $page = $pageModel->find($id)) {
    throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
}
