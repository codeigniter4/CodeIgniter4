<?php

error_reporting(-1);
ini_set('display_errors', 1);

/**
 * This is a helper script that will build out the base information needed
 * for a library reference user guide source file.
 */

require '../system/CLI/CLI.php';

use CodeIgniter\CLI\CLI;

//--------------------------------------------------------------------
// The Class
//--------------------------------------------------------------------

/**
 * Class Document
 *
 * Represents a single document, and provides a simple workflow to
 * read in a source file, generate the results, and then write it out:
 *
 *  Example:
 *      $doc = new Document();
 *      $doc->readSource('path/to/file.php')
 *          ->build()
 *          ->writeTo('user_guide_src/source/...');
 */
class Document
{
	protected $source;
	protected $destination;

	public $prose = '';

	//--------------------------------------------------------------------

	public function readSource(string $source)
	{
		if (! is_file($source))
		{
			CLI::error('Not a valid source file: '. $source);
			die();
		}

	    $this->source = $source;

		return $this;
	}

	//--------------------------------------------------------------------

	public function writeTo(string $dest)
	{
		if (file_exists($dest))
		{
			if ('n' == CLI::prompt('Destination exists. Overwrite?', ['y', 'n']))
			{
				die();
			}
		}

		if (strpos($dest, 'user_guide_src/source/') !== false)
		{
			$dest = substr($dest, strlen('user_guide_src/source/'));
		}

		str_ireplace('.rst', '', $dest);

		$dest = dirname(__FILE__).'/source/'.$dest.'.rst';

		if (! $fp = fopen($dest, 'wb'))
		{
			die('Unable to write to: '. $dest);
		}

		fwrite($fp, $this->prose."\n");
		fclose($fp);
	}

	//--------------------------------------------------------------------

	public function build(string $className)
	{
		require_once $this->source;

		$mirror = new ReflectionClass($className);

		$output  = str_replace('\\', '\\\\', $className)." Class\n";
		$output .= str_repeat('#', strlen($output))."\n\n";

		$output .= $this->cleanDocBlock($mirror->getDocComment())."\n";

		$output .= ".. php:class:: ". str_replace('\\', '\\\\', $className)."\n\n";

		$methods = $mirror->getMethods();

		foreach ($methods as $methodMirror)
		{
			$output .= $this->describeMethod($methodMirror);
		}

		$this->prose = $output;
	}

	//--------------------------------------------------------------------

	protected function describeMethod($methodMirror): string
	{
		if ($methodMirror->isProtected() || $methodMirror->isPrivate()) return '';

		$output = "\t.. php:method:: ".$methodMirror->name." ( ";

		$output .= $this->buildParameterList($methodMirror->getParameters()) ." )\n\n";

		$output .= $this->describeParameters($methodMirror->getParameters());

		if ($methodMirror->hasReturnType())
		{
			$output .= "\t\t:returns: \n\t\t:rtype: {$methodMirror->getReturnType()}";
		}

		$output .= "\n\n";

		$output .= $this->cleanDocBlock($methodMirror->getDocComment())."\n\n";

		return $output;
	}

	//--------------------------------------------------------------------

	protected function buildParameterList(array $params=[]): string
	{
		if (! count($params)) return '';

		$output = '';
		$optionalCount = 0;

		foreach ($params as $paramMirror)
		{
			if ($paramMirror->isOptional())
			{
				$output .= $optionalCount > 0 ? "[, " : "[ ";
				++$optionalCount;
			}

			if ($paramMirror->hasType())
			{
				$output .= (string)$paramMirror->getType()." ";
			}

			if ($paramMirror->isPassedByReference()) $output .= "&";

			if ($paramMirror->isVariadic()) $output .= "...";

			$output .= "\${$paramMirror->getName()} ";
		}

		if ($optionalCount > 0)
		{
			$output .= str_repeat(']', $optionalCount);
		}

		return $output;
	}

	//--------------------------------------------------------------------

	protected function describeParameters(array $params = []): string
	{
		if (! count($params)) return '';

		$output = '';

		foreach ($params as $paramMirror)
		{
			$output .= "\t\t:param ";

			if ($paramMirror->hasType())
			{
				$output .= $paramMirror->getType()." ";
			}

			$output .= "\${$paramMirror->getName()}: \n";
		}

		return $output;
	}

	//--------------------------------------------------------------------

	protected function cleanDocBlock(string $docblock): string
	{
		$lines = explode("\n", $docblock);
		$output = '';

		foreach ($lines as $line)
		{
			$line = trim($line);
			
			if ($line == '/**' || $line == '*/') continue;

			if ($line == '*')
			{
				$output .= "\n";
				continue;
			}

			$line = trim($line);
			
			if (substr($line, 0, 1) == '*')
			{
				$line = trim(substr($line, 1));
			}

			if (substr($line, 0, 1) == '@') continue;

			$output .= "\t\t{$line}\n";
		}

		return $output;
	}

	//--------------------------------------------------------------------

}

//--------------------------------------------------------------------
// Take Input and make it work
//--------------------------------------------------------------------

$source_file = CLI::prompt('Source file');
$source_file = realpath($source_file);

if (empty($source_file))
{
	CLI::error('Unable to locate the source file.');
	die();
}

$source_file = '../system/CLI/CLI.php';

/*
 * Try to determine the class name based on the file
 */
$class_name = 'CodeIgniter\\'. str_ireplace('../system/', '', $source_file);
$class_name = str_replace('.php', '', $class_name);
$class_name = str_replace('/', '\\', $class_name);

$doc = new Document();
$doc->readSource(realpath($source_file))->build($class_name);

$dest_file = CLI::prompt('Destination in user_guide_src');

$doc->writeTo($dest_file);

CLI::write('Done');