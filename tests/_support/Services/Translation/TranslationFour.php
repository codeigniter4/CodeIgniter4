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

class TranslationFour
{
    public function list()
    {
        $translationOne1 = lang('TranslationOne.title');
        $translationOne5 = lang('TranslationOne.last_operation_success');

        $translationThree1 = lang('TranslationThree.alerts.created');
        $translationThree2 = lang('TranslationThree.alerts.failed_insert');

        $translationThree5 = lang('TranslationThree.formFields.new.name');
        $translationThree7 = lang('TranslationThree.formFields.new.short_tag');

        $translationFour1 = lang('Translation-Four.dashed.key-with-dash');
        $translationFour2 = lang('Translation-Four.dashed.key-with-dash-two');
    }
}
