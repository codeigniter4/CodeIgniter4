<?php

catch (\CodeIgniter\UnknownFileException $e) {
    // do something here...

    throw new \RuntimeException($e->getMessage(), $e->getCode(), $e);
}
