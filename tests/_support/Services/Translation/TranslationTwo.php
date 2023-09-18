<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Tests\Support\Services\Translation;

class TranslationTwo
{
    public function list()
    {
        $langKey = 'TranslationTwo.error_key';

        // Error language keys
        $translationError1 = lang('TranslationTwo');
        $translationError2 = lang(' ');
        $translationError3 = lang('');
        $translationError4 = lang('.invalid_key');
        $translationError5 = lang('TranslationTwo.');
        $translationError6 = lang('TranslationTwo...');
        $translationError7 = lang('..invalid_nested_key..');

        // Empty in comments lang('') lang(' ')
    }
}
