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

namespace Utils\PhpCsFixer;

use PhpCsFixer\Config\RuleCustomisationPolicyInterface;
use SplFileInfo;

/**
 * Rule customisation policy for CodeIgniter coding standard.
 *
 * @internal
 */
final class CodeIgniterRuleCustomisationPolicy implements RuleCustomisationPolicyInterface
{
    public function getPolicyVersionForCache(): string
    {
        return hash_file('sha256', __FILE__);
    }

    public function getRuleCustomisers(): array
    {
        $normalisedStrEndsWith = static fn (string $haystack, string $needle): bool => str_ends_with(str_replace('\\', '/', $haystack), $needle);

        return [
            'native_function_casing' => static fn (SplFileInfo $file): bool => ! $normalisedStrEndsWith(
                $file->getPathname(),
                '/tests/system/Database/Live/PreparedQueryTest.php',
            ),
            'ordered_imports' => static fn (SplFileInfo $file): bool => ! $normalisedStrEndsWith(
                $file->getPathname(),
                '/tests/_support/Commands/Foobar.php',
            ),
        ];
    }
}
