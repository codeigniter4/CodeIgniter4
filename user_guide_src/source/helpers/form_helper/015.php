<?php

$js = 'onClick="some_function ()"';
echo form_input('username', 'johndoe', $js);
/*
 * Would produce:
 * <input type="text" name="username" value="johndoe" onClick="some_function ()">
 */
