<?php

if ($pQuery->close()) {
    echo 'Success!';
} else {
    echo 'Deallocation of prepared statements failed!';
}
