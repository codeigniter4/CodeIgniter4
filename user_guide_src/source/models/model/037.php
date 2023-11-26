<?php

// get the rules for all but the "username" field
$rules = $model->getValidationRules(['except' => ['username']]);
// get the rules for only the "city" and "state" fields
$rules = $model->getValidationRules(['only' => ['city', 'state']]);
