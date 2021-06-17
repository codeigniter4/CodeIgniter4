<?php

namespace Tests\Support\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\GeneratorTrait;

class LanguageCommand extends BaseCommand
{
    use GeneratorTrait;

    protected $group       = 'Generators';
    protected $name        = 'publish:language';
    protected $description = 'Publishes a language file.';
    protected $usage       = 'publish:language [options]';
    protected $options     = [
        '--lang' => 'The language folder to save the file.',
        '--sort' => 'Turn on/off the sortImports flag.',
    ];

    public function run(array $params)
    {
        $this->setHasClassName(false);
        $params[0]      = 'Foobar';
        $params['lang'] = $params['lang'] ?? 'en';

        $this->component = 'Language';
        $this->directory = 'Language\\' . $params['lang'];

        $sort = (isset($params['sort']) && $params['sort'] === 'off') ? false : true;
        $this->setSortImports($sort);

        $this->execute($params);
    }

    protected function prepare(string $class): string
    {
        return file_get_contents(__DIR__ . '/Foobar.php') ?: '';
    }
}
