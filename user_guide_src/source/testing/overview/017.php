<?php

public function testSomething()
{
    $curlrequest = $this->getMockBuilder('CodeIgniter\HTTP\CURLRequest')
                        ->setMethods(['request'])
                        ->getMock();
    Services::injectMock('curlrequest', $curlrequest);

    // Do normal testing here....
}
