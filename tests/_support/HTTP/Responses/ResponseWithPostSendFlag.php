<?php

namespace Tests\Support\HTTP\Responses;

use CodeIgniter\HTTP\Response;

class ResponseWithPostSendFlag extends Response
{
    public $responseSent = false;

    /**
     * Sends the output to the browser.
     *
     * @return $this
     */
    public function send()
    {
        parent::send();

        $this->responseSent = true;

        return $this;
    }
}
