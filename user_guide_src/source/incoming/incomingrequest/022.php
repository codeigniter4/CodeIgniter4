<?php

use CodeIgniter\Test\CIUnitTestCase;

final class MyMenuTest extends CIUnitTestCase
{
    public function testActiveLinkUsesCurrentUrl()
    {
        service('request')->setPath('users/list');
        $menu = new MyMenu();
        $this->assertTrue('users/list', $menu->getActiveLink());
    }
}
