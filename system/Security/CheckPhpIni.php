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

namespace CodeIgniter\Security;

use CodeIgniter\CLI\CLI;
use CodeIgniter\View\Table;

/**
 * Checks php.ini settings
 *
 * @used-by \CodeIgniter\Commands\Utilities\PhpIniCheck
 * @see \CodeIgniter\Security\CheckPhpIniTest
 */
class CheckPhpIni
{
    /**
     * @param bool $isCli Set false if you run via Web
     *
     * @return string|void HTML string or void in CLI
     */
    public static function run(bool $isCli = true)
    {
        $output = static::checkIni();

        $thead = ['Directive', 'Global', 'Current', 'Recommended', 'Remark'];
        $tbody = [];

        // CLI
        if ($isCli) {
            self::outputForCli($output, $thead, $tbody);

            return;
        }

        // Web
        return self::outputForWeb($output, $thead, $tbody);
    }

    private static function outputForCli(array $output, array $thead, array $tbody): void
    {
        foreach ($output as $directive => $values) {
            $current        = $values['current'];
            $notRecommended = false;

            if ($values['recommended'] !== '') {
                if ($values['recommended'] !== $values['current']) {
                    $notRecommended = true;
                }

                $current = $notRecommended
                    ? CLI::color($values['current'] === '' ? 'n/a' : $values['current'], 'red')
                    : $values['current'];
            }

            $directive = $notRecommended ? CLI::color($directive, 'red') : $directive;
            $tbody[]   = [
                $directive, $values['global'], $current, $values['recommended'], $values['remark'],
            ];
        }

        CLI::table($tbody, $thead);
    }

    private static function outputForWeb(array $output, array $thead, array $tbody): string
    {
        foreach ($output as $directive => $values) {
            $current        = $values['current'];
            $notRecommended = false;

            if ($values['recommended'] !== '') {
                if ($values['recommended'] !== $values['current']) {
                    $notRecommended = true;
                }

                if ($values['current'] === '') {
                    $current = 'n/a';
                }

                $current = $notRecommended
                    ? '<span style="color: red">' . $current . '</span>'
                    : $current;
            }

            $directive = $notRecommended
                ? '<span style="color: red">' . $directive . '</span>'
                : $directive;
            $tbody[] = [
                $directive, $values['global'], $current, $values['recommended'], $values['remark'],
            ];
        }

        $table    = new Table();
        $template = [
            'table_open' => '<table border="1" cellpadding="4" cellspacing="0">',
        ];
        $table->setTemplate($template);

        $table->setHeading($thead);

        return '<pre>' . $table->generate($tbody) . '</pre>';
    }

    /**
     * @internal Used for testing purposes only.
     * @testTag
     */
    public static function checkIni(): array
    {
        $items = [
            'error_reporting'         => ['recommended' => '5111'],
            'display_errors'          => ['recommended' => '0'],
            'display_startup_errors'  => ['recommended' => '0'],
            'log_errors'              => [],
            'error_log'               => [],
            'default_charset'         => ['recommended' => 'UTF-8'],
            'memory_limit'            => ['remark' => '> post_max_size'],
            'post_max_size'           => ['remark' => '> upload_max_filesize'],
            'upload_max_filesize'     => ['remark' => '< post_max_size'],
            'request_order'           => ['recommended' => 'GP'],
            'variables_order'         => ['recommended' => 'GPCS'],
            'date.timezone'           => ['recommended' => 'UTC'],
            'mbstring.language'       => ['recommended' => 'neutral'],
            'opcache.enable'          => ['recommended' => '1'],
            'opcache.enable_cli'      => [],
            'opcache.jit'             => [],
            'opcache.jit_buffer_size' => [],
        ];

        $output = [];
        $ini    = ini_get_all();

        foreach ($items as $key => $values) {
            $hasKeyInIni  = array_key_exists($key, $ini);
            $output[$key] = [
                'global'      => $hasKeyInIni ? $ini[$key]['global_value'] : 'disabled',
                'current'     => $hasKeyInIni ? $ini[$key]['local_value'] : 'disabled',
                'recommended' => $values['recommended'] ?? '',
                'remark'      => $values['remark'] ?? '',
            ];
        }

        // [directive => [current_value, recommended_value]]
        return $output;
    }
}
