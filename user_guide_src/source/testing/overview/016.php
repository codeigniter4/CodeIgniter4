<?php

use CodeIgniter\Test\CIUnitTestCase;

final class SomeTest extends CIUnitTestCase
{
    public function testSomething()
    {
        $curlrequest = $this->getMockBuilder('CodeIgniter\HTTP\CURLRequest')
            ->setMethods(['request'])
            ->getMock();
        Services::injectMock('curlrequest', $curlrequest);

        // Do normal testing here....
    }
}
