<?php

if (! $request->isSecure()) {
    force_https();
}
