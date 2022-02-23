<?php

public function before(RequestInterface $request, $arguments = null)
{
    $auth = service('auth');

    if (! $auth->isLoggedIn()) {
        return redirect()->to(site_url('login'));
    }
}
