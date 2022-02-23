<?php

cookies()->get(); // array of Cookie objects

// alternatively, you can use the display method
cookies()->display();

// or even from the Response
Services::response()->getCookies();
