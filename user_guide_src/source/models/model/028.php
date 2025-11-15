<?php

$fieldName  = 'username';
$fieldRules = 'required|max_length[30]|alpha_numeric_space|min_length[3]';

$model->setValidationRule($fieldName, $fieldRules);
