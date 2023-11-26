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

namespace CodeIgniter\Validation\StrictRules;

use CodeIgniter\Validation\CreditCardRules as NonStrictCreditCardRules;

/**
 * Class CreditCardRules
 *
 * Provides validation methods for common credit-card inputs.
 *
 * @see http://en.wikipedia.org/wiki/Credit_card_number
 * @see \CodeIgniter\Validation\StrictRules\CreditCardRulesTest
 */
class CreditCardRules
{
    private NonStrictCreditCardRules $nonStrictCreditCardRules;

    public function __construct()
    {
        $this->nonStrictCreditCardRules = new NonStrictCreditCardRules();
    }

    /**
     * Verifies that a credit card number is valid and matches the known
     * formats for a wide number of credit card types. This does not verify
     * that the card is a valid card, only that the number is formatted correctly.
     *
     * Example:
     *  $rules = [
     *      'cc_num' => 'valid_cc_number[visa]'
     *  ];
     *
     * @param array|bool|float|int|object|string|null $ccNumber
     */
    public function valid_cc_number($ccNumber, string $type): bool
    {
        if (! is_string($ccNumber)) {
            return false;
        }

        return $this->nonStrictCreditCardRules->valid_cc_number($ccNumber, $type);
    }
}
