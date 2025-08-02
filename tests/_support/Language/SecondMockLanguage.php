<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Tests\Support\Language;

use CodeIgniter\Language\Language;

/**
 * @phpstan-import-type LoadedStrings from Language
 */
class SecondMockLanguage extends Language
{
    /**
     * Expose the protected *load* method
     *
     * @return ($return is true ? LoadedStrings : null)
     */
    public function loadem(string $file, string $locale = 'en', bool $return = false): ?array
    {
        return $this->load($file, $locale, $return);
    }

    /**
     * Expose the loaded language files
     *
     * @return list<non-empty-string>
     */
    public function loaded(string $locale = 'en'): array
    {
        return $this->loadedFiles[$locale];
    }
}
