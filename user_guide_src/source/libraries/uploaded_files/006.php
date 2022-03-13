<?php

if ($imagefile = $this->request->getFiles()) {
    foreach ($imagefile['images'] as $img) {
        if ($img->isValid() && ! $img->hasMoved()) {
            $newName = $img->getRandomName();
            $img->move(WRITEPATH . 'uploads', $newName);
        }
    }
}
