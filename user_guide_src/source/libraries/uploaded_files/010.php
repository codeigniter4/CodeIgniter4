<?php

if (! $file->isValid()) {
    throw new \RuntimeException($file->getErrorString() . '(' . $file->getError() . ')');
}
