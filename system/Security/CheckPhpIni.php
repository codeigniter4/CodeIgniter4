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
 * Checks php.ini settings in production environment.
 *
 * @used-by \CodeIgniter\Commands\Utilities\PhpIniCheck
 * @see \CodeIgniter\Security\CheckPhpIniTest
 */
class CheckPhpIni
{
    /**
     * @param bool $isCli Set false if you run via Web
     *
     * @return ($isCli is true ? null : string)
     */
    public static function run(bool $isCli = true, ?string $argument = null)
    {
        $output = self::checkIni($argument);

        $thead = ['Directive', 'Global', 'Current', 'Recommended', 'Remark'];

        if ($isCli) {
            self::outputForCli($output, $thead);

            return null;
        }

        return self::outputForWeb($output, $thead);
    }

    /**
     * @param array<string, array{global: string, current: string, recommended: string, remark: string}> $output
     * @param array{string, string, string, string, string}                                              $thead
     */
    private static function outputForCli(array $output, array $thead): void
    {
        $tbody = [];

        foreach ($output as $directive => $values) {
            $current        = $values['current'];
            $notRecommended = false;

            if ($values['recommended'] !== '') {
                if ($values['recommended'] !== $current) {
                    $notRecommended = true;
                }

                $current = $notRecommended
                    ? CLI::color($current === '' ? 'n/a' : $current, 'red')
                    : $current;
            }

            $directive = $notRecommended ? CLI::color($directive, 'red') : $directive;

            $tbody[] = [
                $directive,
                $values['global'],
                $current,
                $values['recommended'],
                $values['remark'],
            ];
        }

        CLI::table($tbody, $thead);
    }

    /**
     * @param array<string, array{global: string, current: string, recommended: string, remark: string}> $output
     * @param array{string, string, string, string, string}                                              $thead
     */
    private static function outputForWeb(array $output, array $thead): string
    {
        $tbody = [];

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
                $directive,
                $values['global'],
                $current,
                $values['recommended'],
                $values['remark'],
            ];
        }

        $table = new Table();
        $table->setTemplate(['table_open' => '<table border="1" cellpadding="4" cellspacing="0">']);
        $table->setHeading($thead);

        return '<pre>' . $table->generate($tbody) . '</pre>';
    }

    /**
     * @return array<string, array{global: string, current: string, recommended: string, remark: string}>
     */
    private static function checkIni(?string $argument = null): array
    {
        // Default items
        $items = [
            'error_reporting'         => ['recommended' => '5111'],
            'display_errors'          => ['recommended' => '0'],
            'display_startup_errors'  => ['recommended' => '0'],
            'log_errors'              => [],
            'error_log'               => [],
            'default_charset'         => ['recommended' => 'UTF-8'],
            'max_execution_time'      => ['remark' => 'The default is 30.'],
            'memory_limit'            => ['remark' => '> post_max_size'],
            'post_max_size'           => ['remark' => '> upload_max_filesize'],
            'upload_max_filesize'     => ['remark' => '< post_max_size'],
            'max_input_vars'          => ['remark' => 'The default is 1000.'],
            'request_order'           => ['recommended' => 'GP'],
            'variables_order'         => ['recommended' => 'GPCS'],
            'date.timezone'           => ['recommended' => 'UTC'],
            'mbstring.language'       => ['recommended' => 'neutral'],
            'opcache.enable'          => ['recommended' => '1'],
            'opcache.enable_cli'      => ['recommended' => '0', 'remark' => 'Enable when you are using queues or running repetitive CLI tasks'],
            'opcache.jit'             => ['recommended' => 'tracing'],
            'opcache.jit_buffer_size' => ['recommended' => '128', 'remark' => 'Adjust with your free space of memory'],
            'zend.assertions'         => ['recommended' => '-1'],
        ];

        if ($argument === 'opcache') {
            $items = [
                'opcache.enable'                  => ['recommended' => '1'],
                'opcache.enable_cli'              => ['recommended' => '0', 'remark' => 'Enable when you are using queues or running repetitive CLI tasks'],
                'opcache.jit'                     => ['recommended' => 'tracing', 'remark' => 'Disable when you used third-party extensions'],
                'opcache.jit_buffer_size'         => ['recommended' => '128', 'remark' => 'Adjust with your free space of memory'],
                'opcache.memory_consumption'      => ['recommended' => '128', 'remark' => 'Adjust with your free space of memory'],
                'opcache.interned_strings_buffer' => ['recommended' => '16'],
                'opcache.max_accelerated_files'   => ['remark' => 'Adjust based on the number of PHP files in your project (e.g.: find your_project/ -iname \'*.php\'|wc -l)'],
                'opcache.max_wasted_percentage'   => ['recommended' => '10'],
                'opcache.validate_timestamps'     => ['recommended' => '0', 'remark' => 'When disabled, opcache will hold your code into shared memory. Restart webserver as needed'],
                'opcache.revalidate_freq'         => [],
                'opcache.file_cache'              => ['remark' => 'Location file caching, It should improve performance when SHM memory is full'],
                'opcache.file_cache_only'         => ['remark' => 'Opcode caching in shared memory, Disabled when you are using Windows'],
                'opcache.file_cache_fallback'     => ['remark' => 'Enable when you are using Windows'],
                'opcache.save_comments'           => ['recommended' => '0', 'remark' => 'Enable when your code requires to read docblock annotations at runtime'],
            ];
        }

        $output = [];

        $ini = ini_get_all();
        assert(is_array($ini));

        foreach ($items as $key => $values) {
            $hasKeyInIni  = array_key_exists($key, $ini);
            $output[$key] = [
                'global'      => $hasKeyInIni ? $ini[$key]['global_value'] : 'disabled',
                'current'     => $hasKeyInIni ? $ini[$key]['local_value'] : 'disabled',
                'recommended' => $values['recommended'] ?? '',
                'remark'      => $values['remark'] ?? '',
            ];
        }

        return $output;
    }
}
