<?php

// In Config\Events.php
Events::on('DBQuery', 'CodeIgniter\Debug\Toolbar\Collectors\Database::collect');
