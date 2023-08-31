<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Config;

use CodeIgniter\View\ViewDecoratorInterface;

/**
 * View configuration
 *
 * @phpstan-type ParserCallable (callable(mixed): mixed)
 * @phpstan-type ParserCallableString (callable(mixed): mixed)&string
 */
class View extends BaseConfig
{
    /**
     * When false, the view method will clear the data between each
     * call.
     *
     * @var bool
     */
    public $saveData = true;

    /**
     * Parser Filters map a filter name with any PHP callable. When the
     * Parser prepares a variable for display, it will chain it
     * through the filters in the order defined, inserting any parameters.
     *
     * To prevent potential abuse, all filters MUST be defined here
     * in order for them to be available for use within the Parser.
     *
     * @psalm-suppress UndefinedDocblockClass
     *
     * @var array<string, string>
     * @phpstan-var array<string, ParserCallableString>
     */
    public $filters = [];

    /**
     * Parser Plugins provide a way to extend the functionality provided
     * by the core Parser by creating aliases that will be replaced with
     * any callable. Can be single or tag pair.
     *
     * @psalm-suppress UndefinedDocblockClass
     *
     * @var array<string, array<string>|callable|string>
     * @phpstan-var array<string, array<ParserCallableString>|ParserCallableString|ParserCallable>
     */
    public $plugins = [];

    /**
     * Built-in View filters.
     *
     * @var array<string, string>
     * @phpstan-var array<string, ParserCallableString>
     */
    protected $coreFilters = [
        'abs'            => '\abs',
        'capitalize'     => '\CodeIgniter\View\Filters::capitalize',
        'date'           => '\CodeIgniter\View\Filters::date',
        'date_modify'    => '\CodeIgniter\View\Filters::date_modify',
        'default'        => '\CodeIgniter\View\Filters::default',
        'esc'            => '\CodeIgniter\View\Filters::esc',
        'excerpt'        => '\CodeIgniter\View\Filters::excerpt',
        'highlight'      => '\CodeIgniter\View\Filters::highlight',
        'highlight_code' => '\CodeIgniter\View\Filters::highlight_code',
        'limit_words'    => '\CodeIgniter\View\Filters::limit_words',
        'limit_chars'    => '\CodeIgniter\View\Filters::limit_chars',
        'local_currency' => '\CodeIgniter\View\Filters::local_currency',
        'local_number'   => '\CodeIgniter\View\Filters::local_number',
        'lower'          => '\strtolower',
        'nl2br'          => '\CodeIgniter\View\Filters::nl2br',
        'number_format'  => '\number_format',
        'prose'          => '\CodeIgniter\View\Filters::prose',
        'round'          => '\CodeIgniter\View\Filters::round',
        'strip_tags'     => '\strip_tags',
        'title'          => '\CodeIgniter\View\Filters::title',
        'upper'          => '\strtoupper',
    ];

    /**
     * Built-in View plugins.
     *
     * @var array<string, array<string>|callable|string>
     * @phpstan-var array<string, array<ParserCallableString>|ParserCallableString|ParserCallable>
     */
    protected $corePlugins = [
        'csp_script_nonce'  => '\CodeIgniter\View\Plugins::cspScriptNonce',
        'csp_style_nonce'   => '\CodeIgniter\View\Plugins::cspStyleNonce',
        'current_url'       => '\CodeIgniter\View\Plugins::currentURL',
        'previous_url'      => '\CodeIgniter\View\Plugins::previousURL',
        'mailto'            => '\CodeIgniter\View\Plugins::mailto',
        'safe_mailto'       => '\CodeIgniter\View\Plugins::safeMailto',
        'lang'              => '\CodeIgniter\View\Plugins::lang',
        'validation_errors' => '\CodeIgniter\View\Plugins::validationErrors',
        'route'             => '\CodeIgniter\View\Plugins::route',
        'siteURL'           => '\CodeIgniter\View\Plugins::siteURL',
    ];

    /**
     * View Decorators are class methods that will be run in sequence to
     * have a chance to alter the generated output just prior to caching
     * the results.
     *
     * All classes must implement CodeIgniter\View\ViewDecoratorInterface
     *
     * @var class-string<ViewDecoratorInterface>[]
     */
    public array $decorators = [];

    /**
     * Merge the built-in and developer-configured filters and plugins,
     * with preference to the developer ones.
     */
    public function __construct()
    {
        $this->filters = array_merge($this->coreFilters, $this->filters);
        $this->plugins = array_merge($this->corePlugins, $this->plugins);

        parent::__construct();
    }
}
