<?php

if (! $this->request->isSecure()) {
    $this->forceHTTPS();
}
