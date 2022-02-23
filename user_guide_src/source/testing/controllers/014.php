<?php

protected function testUnauthorizedAccessRedirects()
{
    $caller = $this->getFilterCaller('permission', 'before');
    $result = $caller('MayEditWidgets');

    $this->assertInstanceOf('CodeIgniter\HTTP\RedirectResponse', $result);
}
