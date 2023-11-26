<?php

$session = session();

$_SESSION['item'];  // But we do not recommend to use superglobal directly.
$session->get('item');
$session->item;
session('item');
