<?php

$users = $userModel->where('active', 1)->findAll();
