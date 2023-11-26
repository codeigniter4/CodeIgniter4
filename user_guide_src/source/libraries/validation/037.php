<?php

class MyRules
{
    public function required_with($value, string $params, array $data): bool
    {
        $params = explode(',', $params);

        // If the field is present we can safely assume that
        // the field is here, no matter whether the corresponding
        // search field is present or not.
        $present = $this->required($value ?? '');

        if ($present) {
            return true;
        }

        // Still here? Then we fail this test if
        // any of the fields are present in $data
        // as $fields is the lis
        $requiredFields = [];

        foreach ($params as $field) {
            if (array_key_exists($field, $data)) {
                $requiredFields[] = $field;
            }
        }

        // Remove any keys with empty values since, that means they
        // weren't truly there, as far as this is concerned.
        $requiredFields = array_filter($requiredFields, static fn ($item) => ! empty($data[$item]));

        return empty($requiredFields);
    }
}
