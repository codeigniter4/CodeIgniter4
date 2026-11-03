<?php

unset($_SESSION['some_name']);
// or multiple values:
unset(
    $_SESSION['some_name'],
    $_SESSION['another_name']
);
