<?php

$fieldName  = 'username';
$fieldRules = 'required|alpha_numeric_space|min_length[3]';

$model->setValidationRule($fieldName, $fieldRules);
