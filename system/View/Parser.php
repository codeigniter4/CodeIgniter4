<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\View;

use CodeIgniter\Autoloader\FileLocator;
use CodeIgniter\View\Exceptions\ViewException;
use Config\View as ViewConfig;
use ParseError;
use Psr\Log\LoggerInterface;

/**
 * Class for parsing pseudo-vars
 *
 * @phpstan-type parser_callable (callable(mixed): mixed)
 * @phpstan-type parser_callable_string (callable(mixed): mixed)&string
 *
 * @see \CodeIgniter\View\ParserTest
 */
class Parser extends View
{
    use ViewDecoratorTrait;

    /**
     * Left delimiter character for pseudo vars
     *
     * @var string
     */
    public $leftDelimiter = '{';

    /**
     * Right delimiter character for pseudo vars
     *
     * @var string
     */
    public $rightDelimiter = '}';

    /**
     * Left delimiter characters for conditionals
     */
    protected string $leftConditionalDelimiter = '{';

    /**
     * Right delimiter characters for conditionals
     */
    protected string $rightConditionalDelimiter = '}';

    /**
     * Stores extracted noparse blocks.
     *
     * @var array
     */
    protected $noparseBlocks = [];

    /**
     * Stores any plugins registered at run-time.
     *
     * @var         array<string, callable|list<string>|string>
     * @phpstan-var array<string, array<parser_callable_string>|parser_callable_string|parser_callable>
     */
    protected $plugins = [];

    /**
     * Stores the context for each data element
     * when set by `setData` so the context is respected.
     *
     * @var array
     */
    protected $dataContexts = [];

    /**
     * Constructor
     *
     * @param FileLocator|null $loader
     */
    public function __construct(ViewConfig $config, ?string $viewPath = null, $loader = null, ?bool $debug = null, ?LoggerInterface $logger = null)
    {
        // Ensure user plugins override core plugins.
        $this->plugins = $config->plugins;

        parent::__construct($config, $viewPath, $loader, $debug, $logger);
    }

    /**
     * Parse a template
     *
     * Parses pseudo-variables contained in the specified template view,
     * replacing them with any data that has already been set.
     */
    public function render(string $view, ?array $options = null, ?bool $saveData = null): string
    {
        $start = microtime(true);
        if ($saveData === null) {
            $saveData = $this->config->saveData;
        }

        $fileExt = pathinfo($view, PATHINFO_EXTENSION);
        $view    = ($fileExt === '') ? $view . '.php' : $view; // allow Views as .html, .tpl, etc (from CI3)

        $cacheName = $options['cache_name'] ?? str_replace('.php', '', $view);

        // Was it cached?
        if (isset($options['cache']) && ($output = cache($cacheName))) {
            $this->logPerformance($start, microtime(true), $view);

            return $output;
        }

        $file = $this->viewPath . $view;

        if (! is_file($file)) {
            $fileOrig = $file;
            $file     = $this->loader->locateFile($view, 'Views');

            // locateFile() will return false if the file cannot be found.
            if ($file === false) {
                throw ViewException::forInvalidFile($fileOrig);
            }
        }

        if ($this->tempData === null) {
            $this->tempData = $this->data;
        }

        $template = file_get_contents($file);
        $output   = $this->parse($template, $this->tempData, $options);
        $this->logPerformance($start, microtime(true), $view);

        if ($saveData) {
            $this->data = $this->tempData;
        }

        $output = $this->decorateOutput($output);

        // Should we cache?
        if (isset($options['cache'])) {
            cache()->save($cacheName, $output, (int) $options['cache']);
        }
        $this->tempData = null;

        return $output;
    }

    /**
     * Parse a String
     *
     * Parses pseudo-variables contained in the specified string,
     * replacing them with any data that has already been set.
     */
    public function renderString(string $template, ?array $options = null, ?bool $saveData = null): string
    {
        $start = microtime(true);
        if ($saveData === null) {
            $saveData = $this->config->saveData;
        }

        if ($this->tempData === null) {
            $this->tempData = $this->data;
        }

        $output = $this->parse($template, $this->tempData, $options);

        $this->logPerformance($start, microtime(true), $this->excerpt($template));

        if ($saveData) {
            $this->data = $this->tempData;
        }

        $this->tempData = null;

        return $output;
    }

    /**
     * Sets several pieces of view data at once.
     * In the Parser, we need to store the context here
     * so that the variable is correctly handled within the
     * parsing itself, and contexts (including raw) are respected.
     *
     * @param         non-empty-string|null                     $context The context to escape it for.
     *                                                                   If 'raw', no escaping will happen.
     * @phpstan-param null|'html'|'js'|'css'|'url'|'attr'|'raw' $context
     */
    public function setData(array $data = [], ?string $context = null): RendererInterface
    {
        if ($context !== null && $context !== '') {
            foreach ($data as $key => &$value) {
                if (is_array($value)) {
                    foreach ($value as &$obj) {
                        $obj = $this->objectToArray($obj);
                    }
                } else {
                    $value = $this->objectToArray($value);
                }

                $this->dataContexts[$key] = $context;
            }
        }

        $this->tempData ??= $this->data;
        $this->tempData = array_merge($this->tempData, $data);

        return $this;
    }

    /**
     * Parse a template
     *
     * Parses pseudo-variables contained in the specified template,
     * replacing them with the data in the second param
     *
     * @param array $options Future options
     */
    protected function parse(string $template, array $data = [], ?array $options = null): string
    {
        if ($template === '') {
            return '';
        }

        // Remove any possible PHP tags since we don't support it
        // and parseConditionals needs it clean anyway...
        $template = str_replace(['<?', '?>'], ['&lt;?', '?&gt;'], $template);

        $template = $this->parseComments($template);
        $template = $this->extractNoparse($template);

        // Replace any conditional code here so we don't have to parse as much
        $template = $this->parseConditionals($template);

        // Handle any plugins before normal data, so that
        // it can potentially modify any template between its tags.
        $template = $this->parsePlugins($template);

        // loop over the data variables, replacing
        // the content as we go.
        foreach ($data as $key => $val) {
            $escape = true;

            if (is_array($val)) {
                $escape  = false;
                $replace = $this->parsePair($key, $val, $template);
            } else {
                $replace = $this->parseSingle($key, (string) $val);
            }

            foreach ($replace as $pattern => $content) {
                $template = $this->replaceSingle($pattern, $content, $template, $escape);
            }
        }

        return $this->insertNoparse($template);
    }

    /**
     * Parse a single key/value, extracting it
     */
    protected function parseSingle(string $key, string $val): array
    {
        $pattern = '#' . $this->leftDelimiter . '!?\s*' . preg_quote($key, '#')
            . '(?(?=\s*\|\s*)(\s*\|*\s*([|\w<>=\(\),:.\-\s\+\\\\/]+)*\s*))(\s*)!?'
            . $this->rightDelimiter . '#ums';

        return [$pattern => $val];
    }

    /**
     * Parse a tag pair
     *
     * Parses tag pairs: {some_tag} string... {/some_tag}
     */
    protected function parsePair(string $variable, array $data, string $template): array
    {
        // Holds the replacement patterns and contents
        // that will be used within a preg_replace in parse()
        $replace = [];

        // Find all matches of space-flexible versions of {tag}{/tag} so we
        // have something to loop over.
        preg_match_all(
            '#' . $this->leftDelimiter . '\s*' . preg_quote($variable, '#') . '\s*' . $this->rightDelimiter . '(.+?)' .
            $this->leftDelimiter . '\s*/' . preg_quote($variable, '#') . '\s*' . $this->rightDelimiter . '#us',
            $template,
            $matches,
            PREG_SET_ORDER
        );

        /*
         * Each match looks like:
         *
         * $match[0] {tag}...{/tag}
         * $match[1] Contents inside the tag
         */
        foreach ($matches as $match) {
            // Loop over each piece of $data, replacing
            // its contents so that we know what to replace in parse()
            $str = '';  // holds the new contents for this tag pair.

            foreach ($data as $row) {
                // Objects that have a `toArray()` method should be
                // converted with that method (i.e. Entities)
                if (is_object($row) && method_exists($row, 'toArray')) {
                    $row = $row->toArray();
                }
                // Otherwise, cast as an array and it will grab public properties.
                elseif (is_object($row)) {
                    $row = (array) $row;
                }

                $temp  = [];
                $pairs = [];
                $out   = $match[1];

                foreach ($row as $key => $val) {
                    // For nested data, send us back through this method...
                    if (is_array($val)) {
                        $pair = $this->parsePair($key, $val, $match[1]);

                        if ($pair !== []) {
                            $pairs[array_keys($pair)[0]] = true;

                            $temp = array_merge($temp, $pair);
                        }

                        continue;
                    }

                    if (is_object($val)) {
                        $val = 'Class: ' . get_class($val);
                    } elseif (is_resource($val)) {
                        $val = 'Resource';
                    }

                    $temp['#' . $this->leftDelimiter . '!?\s*' . preg_quote($key, '#') . '(?(?=\s*\|\s*)(\s*\|*\s*([|\w<>=\(\),:.\-\s\+\\\\/]+)*\s*))(\s*)!?' . $this->rightDelimiter . '#us'] = $val;
                }

                // Now replace our placeholders with the new content.
                foreach ($temp as $pattern => $content) {
                    $out = $this->replaceSingle($pattern, $content, $out, ! isset($pairs[$pattern]));
                }

                $str .= $out;
            }

            $escapedMatch = preg_quote($match[0], '#');

            $replace['#' . $escapedMatch . '#us'] = $str;
        }

        return $replace;
    }

    /**
     * Removes any comments from the file. Comments are wrapped in {# #} symbols:
     *
     *      {# This is a comment #}
     */
    protected function parseComments(string $template): string
    {
        return preg_replace('/\{#.*?#\}/us', '', $template);
    }

    /**
     * Extracts noparse blocks, inserting a hash in its place so that
     * those blocks of the page are not touched by parsing.
     */
    protected function extractNoparse(string $template): string
    {
        $pattern = '/\{\s*noparse\s*\}(.*?)\{\s*\/noparse\s*\}/ums';

        /*
         * $matches[][0] is the raw match
         * $matches[][1] is the contents
         */
        if (preg_match_all($pattern, $template, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                // Create a hash of the contents to insert in its place.
                $hash                       = md5($match[1]);
                $this->noparseBlocks[$hash] = $match[1];
                $template                   = str_replace($match[0], "noparse_{$hash}", $template);
            }
        }

        return $template;
    }

    /**
     * Re-inserts the noparsed contents back into the template.
     */
    public function insertNoparse(string $template): string
    {
        foreach ($this->noparseBlocks as $hash => $replace) {
            $template = str_replace("noparse_{$hash}", $replace, $template);
            unset($this->noparseBlocks[$hash]);
        }

        return $template;
    }

    /**
     * Parses any conditionals in the code, removing blocks that don't
     * pass so we don't try to parse it later.
     *
     * Valid conditionals:
     *  - if
     *  - elseif
     *  - else
     */
    protected function parseConditionals(string $template): string
    {
        $leftDelimiter  = preg_quote($this->leftConditionalDelimiter, '/');
        $rightDelimiter = preg_quote($this->rightConditionalDelimiter, '/');

        $pattern = '/'
            . $leftDelimiter
            . '\s*(if|elseif)\s*((?:\()?(.*?)(?:\))?)\s*'
            . $rightDelimiter
            . '/ums';

        /*
         * For each match:
         * [0] = raw match `{if var}`
         * [1] = conditional `if`
         * [2] = condition `do === true`
         * [3] = same as [2]
         */
        preg_match_all($pattern, $template, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            // Build the string to replace the `if` statement with.
            $condition = $match[2];

            $statement = $match[1] === 'elseif' ? '<?php elseif (' . $condition . '): ?>' : '<?php if (' . $condition . '): ?>';
            $template  = str_replace($match[0], $statement, $template);
        }

        $template = preg_replace(
            '/' . $leftDelimiter . '\s*else\s*' . $rightDelimiter . '/ums',
            '<?php else: ?>',
            $template
        );
        $template = preg_replace(
            '/' . $leftDelimiter . '\s*endif\s*' . $rightDelimiter . '/ums',
            '<?php endif; ?>',
            $template
        );

        // Parse the PHP itself, or insert an error so they can debug
        ob_start();

        if ($this->tempData === null) {
            $this->tempData = $this->data;
        }

        extract($this->tempData);

        try {
            eval('?>' . $template . '<?php ');
        } catch (ParseError $e) {
            ob_end_clean();

            throw ViewException::forTagSyntaxError(str_replace(['?>', '<?php '], '', $template));
        }

        return ob_get_clean();
    }

    /**
     * Over-ride the substitution field delimiters.
     *
     * @param string $leftDelimiter
     * @param string $rightDelimiter
     */
    public function setDelimiters($leftDelimiter = '{', $rightDelimiter = '}'): RendererInterface
    {
        $this->leftDelimiter  = $leftDelimiter;
        $this->rightDelimiter = $rightDelimiter;

        return $this;
    }

    /**
     * Over-ride the substitution conditional delimiters.
     *
     * @param string $leftDelimiter
     * @param string $rightDelimiter
     */
    public function setConditionalDelimiters($leftDelimiter = '{', $rightDelimiter = '}'): RendererInterface
    {
        $this->leftConditionalDelimiter  = $leftDelimiter;
        $this->rightConditionalDelimiter = $rightDelimiter;

        return $this;
    }

    /**
     * Handles replacing a pseudo-variable with the actual content. Will double-check
     * for escaping brackets.
     *
     * @param array|string $pattern
     * @param string       $content
     * @param string       $template
     */
    protected function replaceSingle($pattern, $content, $template, bool $escape = false): string
    {
        $content = (string) $content;

        // Replace the content in the template
        return preg_replace_callback($pattern, function ($matches) use ($content, $escape) {
            // Check for {! !} syntax to not escape this one.
            if (
                strpos($matches[0], $this->leftDelimiter . '!') === 0
                && substr($matches[0], -1 - strlen($this->rightDelimiter)) === '!' . $this->rightDelimiter
            ) {
                $escape = false;
            }

            return $this->prepareReplacement($matches, $content, $escape);
        }, (string) $template);
    }

    /**
     * Callback used during parse() to apply any filters to the value.
     */
    protected function prepareReplacement(array $matches, string $replace, bool $escape = true): string
    {
        $orig = array_shift($matches);

        // Our regex earlier will leave all chained values on a single line
        // so we need to break them apart so we can apply them all.
        $filters = (isset($matches[1]) && $matches[1] !== '') ? explode('|', $matches[1]) : [];

        if ($escape && $filters === [] && ($context = $this->shouldAddEscaping($orig))) {
            $filters[] = "esc({$context})";
        }

        return $this->applyFilters($replace, $filters);
    }

    /**
     * Checks the placeholder the view provided to see if we need to provide any autoescaping.
     *
     * @return false|string
     */
    public function shouldAddEscaping(string $key)
    {
        $escape = false;

        $key = trim(str_replace(['{', '}'], '', $key));

        // If the key has a context stored (from setData)
        // we need to respect that.
        if (array_key_exists($key, $this->dataContexts)) {
            if ($this->dataContexts[$key] !== 'raw') {
                return $this->dataContexts[$key];
            }
        }
        // No pipes, then we know we need to escape
        elseif (strpos($key, '|') === false) {
            $escape = 'html';
        }
        // If there's a `noescape` then we're definitely false.
        elseif (strpos($key, 'noescape') !== false) {
            $escape = false;
        }
        // If no `esc` filter is found, then we'll need to add one.
        elseif (! preg_match('/\s+esc/u', $key)) {
            $escape = 'html';
        }

        return $escape;
    }

    /**
     * Given a set of filters, will apply each of the filters in turn
     * to $replace, and return the modified string.
     */
    protected function applyFilters(string $replace, array $filters): string
    {
        // Determine the requested filters
        foreach ($filters as $filter) {
            // Grab any parameter we might need to send
            preg_match('/\([\w<>=\/\\\,:.\-\s\+]+\)/u', $filter, $param);

            // Remove the () and spaces to we have just the parameter left
            $param = ($param !== []) ? trim($param[0], '() ') : null;

            // Params can be separated by commas to allow multiple parameters for the filter
            if ($param !== null && $param !== '') {
                $param = explode(',', $param);

                // Clean it up
                foreach ($param as &$p) {
                    $p = trim($p, ' "');
                }
            } else {
                $param = [];
            }

            // Get our filter name
            $filter = $param !== [] ? trim(strtolower(substr($filter, 0, strpos($filter, '(')))) : trim($filter);

            if (! array_key_exists($filter, $this->config->filters)) {
                continue;
            }

            // Filter it....
            $replace = $this->config->filters[$filter]($replace, ...$param);
        }

        return $replace;
    }

    // Plugins

    /**
     * Scans the template for any parser plugins, and attempts to execute them.
     * Plugins are delimited by {+ ... +}
     *
     * @return string
     */
    protected function parsePlugins(string $template)
    {
        foreach ($this->plugins as $plugin => $callable) {
            // Paired tags are enclosed in an array in the config array.
            $isPair   = is_array($callable);
            $callable = $isPair ? array_shift($callable) : $callable;

            // See https://regex101.com/r/BCBBKB/1
            $pattern = $isPair
                ? '#\{\+\s*' . $plugin . '([\w=\-_:\+\s\(\)/"@.]*)?\s*\+\}(.+?)\{\+\s*/' . $plugin . '\s*\+\}#uims'
                : '#\{\+\s*' . $plugin . '([\w=\-_:\+\s\(\)/"@.]*)?\s*\+\}#uims';

            /**
             * Match tag pairs
             *
             * Each match is an array:
             *   $matches[0] = entire matched string
             *   $matches[1] = all parameters string in opening tag
             *   $matches[2] = content between the tags to send to the plugin.
             */
            if (! preg_match_all($pattern, $template, $matches, PREG_SET_ORDER)) {
                continue;
            }

            foreach ($matches as $match) {
                $params = [];

                preg_match_all('/([\w-]+=\"[^"]+\")|([\w-]+=[^\"\s=]+)|(\"[^"]+\")|(\S+)/u', trim($match[1]), $matchesParams);

                foreach ($matchesParams[0] as $item) {
                    $keyVal = explode('=', $item);

                    if (count($keyVal) === 2) {
                        $params[$keyVal[0]] = str_replace('"', '', $keyVal[1]);
                    } else {
                        $params[] = str_replace('"', '', $item);
                    }
                }

                $template = $isPair
                    ? str_replace($match[0], $callable($match[2], $params), $template)
                    : str_replace($match[0], $callable($params), $template);
            }
        }

        return $template;
    }

    /**
     * Makes a new plugin available during the parsing of the template.
     *
     * @return $this
     */
    public function addPlugin(string $alias, callable $callback, bool $isPair = false)
    {
        $this->plugins[$alias] = $isPair ? [$callback] : $callback;

        return $this;
    }

    /**
     * Removes a plugin from the available plugins.
     *
     * @return $this
     */
    public function removePlugin(string $alias)
    {
        unset($this->plugins[$alias]);

        return $this;
    }

    /**
     * Converts an object to an array, respecting any
     * toArray() methods on an object.
     *
     * @param array|bool|float|int|object|string|null $value
     *
     * @return array|bool|float|int|string|null
     */
    protected function objectToArray($value)
    {
        // Objects that have a `toArray()` method should be
        // converted with that method (i.e. Entities)
        if (is_object($value) && method_exists($value, 'toArray')) {
            $value = $value->toArray();
        }
        // Otherwise, cast as an array and it will grab public properties.
        elseif (is_object($value)) {
            $value = (array) $value;
        }

        return $value;
    }
}
