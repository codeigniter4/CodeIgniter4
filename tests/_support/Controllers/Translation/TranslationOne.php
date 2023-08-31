<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Tests\Support\Controllers\Translation;

class TranslationOne
{
    public function list()
    {
        $translationOne1 = lang('TranslationOne.title');
        $translationOne2 = lang('TranslationOne.DESCRIPTION');
        $translationOne3 = lang('TranslationOne.metaTags');
        $translationOne4 = lang('TranslationOne.Copyright');
        $translationOne5 = lang('TranslationOne.last_operation_success');

        $translationThree1 = lang('TranslationThree.alerts.created');
        $translationThree2 = lang('TranslationThree.alerts.failed_insert');
        $translationThree3 = lang('TranslationThree.alerts.Updated');
        $translationThree4 = lang('TranslationThree.alerts.DELETED');

        $translationThree5  = lang('TranslationThree.formFields.new.name');
        $translationThree6  = lang('TranslationThree.formFields.new.TEXT');
        $translationThree7  = lang('TranslationThree.formFields.new.short_tag');
        $translationThree8  = lang('TranslationThree.formFields.edit.name');
        $translationThree9  = lang('TranslationThree.formFields.edit.TEXT');
        $translationThree10 = lang('TranslationThree.formFields.edit.short_tag');
    }
}
