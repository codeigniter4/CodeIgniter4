<?php

$html = $parser->setData(['first_name' => $user->first_name])
    ->renderString($emailTemplate, ['restrictConditionals' => true]);
