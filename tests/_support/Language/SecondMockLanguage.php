<?php

namespace Tests\Support\Language;

use CodeIgniter\Language\Language;

class SecondMockLanguage extends Language
{
	/**
	 * Expose the protected *load* method
	 *
	 * @param string $file
	 * @param string $locale
	 * @param bool   $return
	 */
	public function loadem(string $file, string $locale = 'en', bool $return = false)
	{
		return $this->load($file, $locale, $return);
	}

	/**
	 * Expose the loaded language files
	 *
	 * @param string $locale
	 */
	public function loaded(string $locale = 'en')
	{
		return $this->loadedFiles[$locale];
	}
}
