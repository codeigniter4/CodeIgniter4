<?php

if ($file->isValid() && ! $file->hasMoved()) {
    $file->move($path);
}
