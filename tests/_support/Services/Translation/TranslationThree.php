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

namespace Tests\Support\Services\Translation;

class TranslationThree
{
    public function list(): void
    {
        lang('TranslationOne.title');
        lang('TranslationOne.DESCRIPTION');
        lang('TranslationOne.subTitle');
        lang('TranslationOne.overflow_style');

        lang('TranslationThree.alerts.created');
        lang('TranslationThree.alerts.failed_insert');

        lang('TranslationThree.formFields.new.name');
        lang('TranslationThree.formFields.new.TEXT');
        lang('TranslationThree.formFields.new.short_tag');

        lang('TranslationThree.alerts.CANCELED');
        lang('TranslationThree.alerts.missing_keys');

        lang('TranslationThree.formErrors.edit.empty_name');
        lang('TranslationThree.formErrors.edit.INVALID_TEXT');
        lang('TranslationThree.formErrors.edit.missing_short_tag');
    }
}
