<?php

// on Windows environment
$fileOnWindows = 'C:\App/Users\User\Desktop/bar.php';
echo normalize_path($fileOnWindows); // 'C:\App\Users\User\Desktop\bar.php'
echo normalize_path($fileOnWindows, false); // 'C:/App/Users/User/Desktop/bar.php'

// on Linux environment
$fileOnLinux = 'var\socket/tmp';
echo normalize_path($fileOnLinux); // 'var/socket/tmp'
echo normalize_path($fileOnLinux, false); // 'var/socket/tmp'
