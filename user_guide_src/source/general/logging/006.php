<?php

try {
    // Something throws error here
} catch (\Exception $e) {
    log_message('error', '[ERROR] {exception}', ['exception' => $e]);
}
