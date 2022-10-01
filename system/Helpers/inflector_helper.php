<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

// CodeIgniter Inflector Helpers

if (! function_exists('singular')) {
    /**
     * Singular
     *
     * Takes a plural word and makes it singular
     *
     * @param string $string Input string
     */
    function singular(string $string): string
    {
        $result = $string;

        if (! is_pluralizable($result)) {
            return $result;
        }

        // Arranged in order.
        $singularRules = [
            '/(matr)ices$/'                                                   => '\1ix',
            '/(vert|ind)ices$/'                                               => '\1ex',
            '/^(ox)en/'                                                       => '\1',
            '/(alias)es$/'                                                    => '\1',
            '/([octop|vir])i$/'                                               => '\1us',
            '/(cris|ax|test)es$/'                                             => '\1is',
            '/(shoe)s$/'                                                      => '\1',
            '/(o)es$/'                                                        => '\1',
            '/(bus|campus)es$/'                                               => '\1',
            '/([m|l])ice$/'                                                   => '\1ouse',
            '/(x|ch|ss|sh)es$/'                                               => '\1',
            '/(m)ovies$/'                                                     => '\1\2ovie',
            '/(s)eries$/'                                                     => '\1\2eries',
            '/([^aeiouy]|qu)ies$/'                                            => '\1y',
            '/([lr])ves$/'                                                    => '\1f',
            '/(tive)s$/'                                                      => '\1',
            '/(hive)s$/'                                                      => '\1',
            '/([^f])ves$/'                                                    => '\1fe',
            '/(^analy)ses$/'                                                  => '\1sis',
            '/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/' => '\1\2sis',
            '/([ti])a$/'                                                      => '\1um',
            '/(p)eople$/'                                                     => '\1\2erson',
            '/(m)en$/'                                                        => '\1an',
            '/(s)tatuses$/'                                                   => '\1\2tatus',
            '/(c)hildren$/'                                                   => '\1\2hild',
            '/(n)ews$/'                                                       => '\1\2ews',
            '/(quiz)zes$/'                                                    => '\1',
            '/([^us])s$/'                                                     => '\1',
        ];

        foreach ($singularRules as $rule => $replacement) {
            if (preg_match($rule, $result)) {
                $result = preg_replace($rule, $replacement, $result);
                break;
            }
        }

        return $result;
    }
}

if (! function_exists('plural')) {
    /**
     * Plural
     *
     * Takes a singular word and makes it plural
     *
     * @param string $string Input string
     */
    function plural(string $string): string
    {
        $result = $string;

        if (! is_pluralizable($result)) {
            return $result;
        }

        $pluralRules = [
            '/(quiz)$/'               => '\1zes',    // quizzes
            '/^(ox)$/'                => '\1\2en', // ox
            '/([m|l])ouse$/'          => '\1ice', // mouse, louse
            '/(matr|vert|ind)ix|ex$/' => '\1ices', // matrix, vertex, index
            '/(x|ch|ss|sh)$/'         => '\1es', // search, switch, fix, box, process, address
            '/([^aeiouy]|qu)y$/'      => '\1ies', // query, ability, agency
            '/(hive)$/'               => '\1s', // archive, hive
            '/(?:([^f])fe|([lr])f)$/' => '\1\2ves', // half, safe, wife
            '/sis$/'                  => 'ses', // basis, diagnosis
            '/([ti])um$/'             => '\1a', // datum, medium
            '/(p)erson$/'             => '\1eople', // person, salesperson
            '/(m)an$/'                => '\1en', // man, woman, spokesman
            '/(c)hild$/'              => '\1hildren', // child
            '/(buffal|tomat)o$/'      => '\1\2oes', // buffalo, tomato
            '/(bu|campu)s$/'          => '\1\2ses', // bus, campus
            '/(alias|status|virus)$/' => '\1es', // alias
            '/(octop)us$/'            => '\1i', // octopus
            '/(ax|cris|test)is$/'     => '\1es', // axis, crisis
            '/s$/'                    => 's', // no change (compatibility)
            '/$/'                     => 's',
        ];

        foreach ($pluralRules as $rule => $replacement) {
            if (preg_match($rule, $result)) {
                $result = preg_replace($rule, $replacement, $result);
                break;
            }
        }

        return $result;
    }
}

if (! function_exists('counted')) {
    /**
     * Counted
     *
     * Takes a number and a word to return the plural or not
     * E.g. 0 cats, 1 cat, 2 cats, ...
     *
     * @param int    $count  Number of items
     * @param string $string Input string
     */
    function counted(int $count, string $string): string
    {
        $result = "{$count} ";

        return $result . ($count === 1 ? singular($string) : plural($string));
    }
}

if (! function_exists('camelize')) {
    /**
     * Camelize
     *
     * Takes multiple words separated by spaces or
     * underscores and converts them to camel case.
     *
     * @param string $string Input string
     */
    function camelize(string $string): string
    {
        return lcfirst(str_replace(' ', '', ucwords(preg_replace('/[\s_]+/', ' ', $string))));
    }
}

if (! function_exists('pascalize')) {
    /**
     * Pascalize
     *
     * Takes multiple words separated by spaces or
     * underscores and converts them to Pascal case,
     * which is camel case with an uppercase first letter.
     *
     * @param string $string Input string
     */
    function pascalize(string $string): string
    {
        return ucfirst(camelize($string));
    }
}

if (! function_exists('underscore')) {
    /**
     * Underscore
     *
     * Takes multiple words separated by spaces and underscores them
     *
     * @param string $string Input string
     */
    function underscore(string $string): string
    {
        $replacement = trim($string);

        return preg_replace('/[\s]+/', '_', $replacement);
    }
}

if (! function_exists('decamelize')) {
    /**
     * Decamelize
     *
     * Takes multiple words separated by camel case and
     * underscores them.
     *
     * @param string $string Input string
     */
    function decamelize(string $string): string
    {
        return strtolower(preg_replace(['/([a-z\d])([A-Z])/', '/([^_])([A-Z][a-z])/'], '$1_$2', trim($string)));
    }
}

if (! function_exists('humanize')) {
    /**
     * Humanize
     *
     * Takes multiple words separated by the separator,
     * camelizes and changes them to spaces
     *
     * @param string $string    Input string
     * @param string $separator Input separator
     */
    function humanize(string $string, string $separator = '_'): string
    {
        $replacement = trim($string);

        return ucwords(preg_replace('/[' . $separator . ']+/', ' ', $replacement));
    }
}

if (! function_exists('is_pluralizable')) {
    /**
     * Checks if the given word has a plural version.
     *
     * @param string $word Word to check
     */
    function is_pluralizable(string $word): bool
    {
        $uncountables = in_array(
            strtolower($word),
            [
                'advice',
                'bravery',
                'butter',
                'chaos',
                'clarity',
                'coal',
                'courage',
                'cowardice',
                'curiosity',
                'education',
                'equipment',
                'evidence',
                'fish',
                'fun',
                'furniture',
                'greed',
                'help',
                'homework',
                'honesty',
                'information',
                'insurance',
                'jewelry',
                'knowledge',
                'livestock',
                'love',
                'luck',
                'marketing',
                'meta',
                'money',
                'mud',
                'news',
                'patriotism',
                'racism',
                'rice',
                'satisfaction',
                'scenery',
                'series',
                'sexism',
                'silence',
                'species',
                'spelling',
                'sugar',
                'water',
                'weather',
                'wisdom',
                'work',
            ],
            true
        );

        return ! $uncountables;
    }
}

if (! function_exists('dasherize')) {
    /**
     * Replaces underscores with dashes in the string.
     *
     * @param string $string Input string
     */
    function dasherize(string $string): string
    {
        return str_replace('_', '-', $string);
    }
}

if (! function_exists('ordinal')) {
    /**
     * Returns the suffix that should be added to a
     * number to denote the position in an ordered
     * sequence such as 1st, 2nd, 3rd, 4th.
     *
     * @param int $integer The integer to determine the suffix
     */
    function ordinal(int $integer): string
    {
        $suffixes = [
            'th',
            'st',
            'nd',
            'rd',
            'th',
            'th',
            'th',
            'th',
            'th',
            'th',
        ];

        return $integer % 100 >= 11 && $integer % 100 <= 13 ? 'th' : $suffixes[$integer % 10];
    }
}

if (! function_exists('ordinalize')) {
    /**
     * Turns a number into an ordinal string used
     * to denote the position in an ordered sequence
     * such as 1st, 2nd, 3rd, 4th.
     *
     * @param int $integer The integer to ordinalize
     */
    function ordinalize(int $integer): string
    {
        return $integer . ordinal($integer);
    }
}
