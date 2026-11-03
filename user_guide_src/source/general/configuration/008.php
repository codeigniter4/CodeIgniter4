<?php

namespace Config;

// ...

class MyConfig extends BaseConfig
{
    public static $registrars = [
        SupportingPackageRegistrar::class,
    ];

    // ...
}
