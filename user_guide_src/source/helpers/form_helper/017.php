<?php

echo form_input('email', 'joe@example.com', ['placeholder' => 'Email Address...'], 'email');
/*
 * Would produce:
 * <input type="email" name="email" value="joe@example.com" placeholder="Email Address...">
 */
