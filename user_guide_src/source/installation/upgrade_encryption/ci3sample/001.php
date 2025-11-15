<?php

$this->load->library('encryption');

$plain_text = 'This is a plain-text message!';
$ciphertext = $this->encryption->encrypt($plain_text);

// Outputs: This is a plain-text message!
echo $this->encryption->decrypt($ciphertext);
