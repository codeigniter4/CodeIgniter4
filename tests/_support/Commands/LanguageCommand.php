<?php

namespace Tests\Support\Commands;

use CodeIgniter\CLI\GeneratorCommand;

class LanguageCommand extends GeneratorCommand
{
	protected $name        = 'publish:language';
	protected $description = 'Publishes a language file.';
	protected $usage       = 'publish:language [options]';
	protected $options     = [
		'--lang' => 'The language folder to save the file.',
		'--sort' => 'Turn on/off the sortImports flag.',
	];

	public function run(array $params)
	{
		$params[0]      = 'Foobar';
		$params['lang'] = $params['lang'] ?? 'en';
		$sort           = (isset($params['sort']) && $params['sort'] === 'off') ? false : true;

		$this->setSortImports($sort);

		parent::run($params);
	}

	protected function getNamespacedClass(string $rootNamespace, string $class): string
	{
		return $rootNamespace . '\\Language\\' . $this->params['lang'] . '\\' . $class;
	}

	protected function getTemplate(): string
	{
		return file_get_contents(__DIR__ . '/Foobar.php') ?: '';
	}
}
