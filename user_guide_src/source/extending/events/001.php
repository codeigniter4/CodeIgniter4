<?php

use CodeIgniter\Events\Events;

Events::on('pre_system', ['MyClass', 'myFunction']);
