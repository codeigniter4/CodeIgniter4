<?php

namespace Tests\Support\Language;

use CodeIgniter\Language\Language;

class SecondMockLanguage extends Language
{
    //--------------------------------------------------------------------

    /**
     * Expose the protected *load* method
     */
    public function loadem(string $file, string $locale = 'en', bool $return = false)
    {
        return $this->load($file, $locale, $return);
    }

    //--------------------------------------------------------------------

    /**
     * Expose the loaded language files
     */
    public function loaded(string $locale = 'en')
    {
        return $this->loadedFiles[$locale];
    }
}
