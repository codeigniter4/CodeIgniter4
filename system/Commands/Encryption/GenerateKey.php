<?php

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2019 British Columbia Institute of Technology
 * Copyright (c) 2019-2020 CodeIgniter Foundation
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package    CodeIgniter
 * @author     CodeIgniter Dev Team
 * @copyright  2019-2020 CodeIgniter Foundation
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link       https://codeigniter.com
 * @since      Version 4.0.0
 * @filesource
 */

namespace CodeIgniter\Commands\Encryption;

use CodeIgniter\Config\DotEnv;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Encryption\Encryption;

/**
 * Generates a new encryption key.
 */
class GenerateKey extends BaseCommand
{
	/**
	 * The Command's group.
	 *
	 * @var string
	 */
	protected $group = 'Encryption';

	/**
	 * The Command's name.
	 *
	 * @var string
	 */
	protected $name = 'key:generate';

	/**
	 * The Command's usage.
	 *
	 * @var string
	 */
	protected $usage = 'key:generate [options]';

	/**
	 * The Command's short description.
	 *
	 * @var string
	 */
	protected $description = 'Generates a new encryption key.';

	/**
	 * The command's options
	 *
	 * @var array
	 */
	protected $options = [
		'-encoding' => 'Encoding to use (either hex or base64)',
		'-force'    => 'Force overwrite existing key.',
		'-show'     => 'Shows the generated key in the terminal rather than storing in the .env file.',
	];

	/**
	 * Actually execute the command.
	 *
	 * @param array $params
	 *
	 * @return void
	 */
	public function run(array $params)
	{
		$encoding = array_key_exists('encoding', $params) ? $params['encoding'] : CLI::getOption('encoding');
		if ($encoding === null || ! in_array($encoding, ['hex', 'base64'], true))
		{
			// @codeCoverageIgnoreStart
			$encoding = CLI::prompt('Please provide a valid encoding to use.', ['hex', 'base64'], 'required');
			// @codeCoverageIgnoreEnd
		}

		$encodedKey = $this->generateRandomKey($encoding);
		if (array_key_exists('show', $params) || (bool) CLI::getOption('show'))
		{
			CLI::write($encodedKey, 'yellow');
			CLI::newLine();
			return;
		}

		if (! $this->setNewEncryptionKey($encodedKey, $params))
		{
			// @codeCoverageIgnoreStart
			CLI::error('Error in creating new encryption key.');
			CLI::newLine();
			return;
			// @codeCoverageIgnoreEnd
		}

		// force DotEnv to reload the new env vars
		putenv('encryption.key');
		unset($_ENV['encryption.key'], $_SERVER['encryption.key']);
		$dotenv = new DotEnv(ROOTPATH);
		$dotenv->load();

		CLI::write('Application\'s new encryption key was successfully set.', 'green');
		CLI::newLine();
	}

	/**
	 * Generates a key and encodes it.
	 *
	 * @param string $encoding
	 *
	 * @return string
	 */
	protected function generateRandomKey(string $encoding): string
	{
		$key = Encryption::createKey();

		if ($encoding === 'hex')
		{
			return 'hex2bin:' . bin2hex($key);
		}

		return 'base64:' . base64_encode($key);
	}

	/**
	 * Sets the new encryption key in your .env file.
	 *
	 * @param string $key
	 * @param array  $params
	 *
	 * @return boolean
	 */
	protected function setNewEncryptionKey(string $key, array $params): bool
	{
		$currentKey = env('encryption.key', '');

		if (strlen($currentKey) !== 0 && ! $this->confirmOverwrite($params))
		{
			// Not yet testable since it requires keyboard input
			// @codeCoverageIgnoreStart
			return false;
			// @codeCoverageIgnoreEnd
		}

		return $this->writeNewEncryptionKeyToFile($currentKey, $key);
	}

	protected function confirmOverwrite(array $params): bool
	{
		return (array_key_exists('force', $params) || CLI::getOption('force')) || CLI::prompt('Overwrite existing key?', ['n', 'y']) === 'y';
	}

	/**
	 * Writes the new encryption key to .env file.
	 *
	 * @param string $key
	 *
	 * @return boolean
	 */
	protected function writeNewEncryptionKeyToFile(string $oldKey, string $newKey): bool
	{
		$envFile = ROOTPATH . '.env';

		if (! file_exists($envFile))
		{
			copy(ROOTPATH . 'env', $envFile);
		}

		$ret = file_put_contents($envFile, preg_replace(
			$this->keyPattern($oldKey),
			"\nencryption.key = $newKey",
			file_get_contents($envFile)
		));

		return $ret !== false;
	}

	/**
	 * Get the regex of the current encryption key.
	 *
	 * @param string $oldKey
	 *
	 * @return string
	 */
	protected function keyPattern(string $oldKey): string
	{
		$escaped = preg_quote($oldKey, '/');

		if ($escaped !== '')
		{
			$escaped = "[$escaped]*";
		}

		return "/^[#\s]*encryption.key[=\s]*{$escaped}$/m";
	}
}
