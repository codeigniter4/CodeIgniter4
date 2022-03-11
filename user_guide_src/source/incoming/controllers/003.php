<?php

if (! $this->request->isSecure()) {
    $this->forceHTTPS(31536000); // one year
}
