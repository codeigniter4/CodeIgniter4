<?php

cookies()->display(); // array of Cookie objects

// or even from the Response
service('response')->getCookies();
