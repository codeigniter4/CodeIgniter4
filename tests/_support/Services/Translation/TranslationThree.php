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

class TranslationThree
{
    public function list()
    {
        $translationOne1 = lang('TranslationOne.title');
        $translationOne2 = lang('TranslationOne.DESCRIPTION');
        $translationOne6 = lang('TranslationOne.subTitle');
        $translationOne7 = lang('TranslationOne.overflow_style');

        $translationThree1 = lang('TranslationThree.alerts.created');
        $translationThree2 = lang('TranslationThree.alerts.failed_insert');

        $translationThree5 = lang('TranslationThree.formFields.new.name');
        $translationThree6 = lang('TranslationThree.formFields.new.TEXT');
        $translationThree7 = lang('TranslationThree.formFields.new.short_tag');

        $translationThree11 = lang('TranslationThree.alerts.CANCELED');
        $translationThree12 = lang('TranslationThree.alerts.missing_keys');

        $translationThree13 = lang('TranslationThree.formErrors.edit.empty_name');
        $translationThree14 = lang('TranslationThree.formErrors.edit.INVALID_TEXT');
        $translationThree15 = lang('TranslationThree.formErrors.edit.missing_short_tag');
    }
}
