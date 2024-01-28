<?php

use CodeIgniter\Events\Events;

Events::on('post_controller_constructor', 'some_function', 25);
