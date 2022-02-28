<?php

class View extends BaseView
{
    public $plugins = [
        'foo' => ['\Some\Class::methodName'],
    ];
}

// {+ foo +} inner content {+ /foo +}
