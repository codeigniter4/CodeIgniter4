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

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Config\DotEnv;
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
	protected $description = 'Generates a new encryption key and writes it in an `.env` file.';

	/**
	 * The command's options
	 *
	 * @var array
	 */
	protected $options = [
		'-force'  => 'Force overwrite existing key in `.env` file.',
		'-length' => 'The length of the random string that should be returned in bytes. Defaults to 32.',
		'-prefix' => 'Prefix to prepend to encoded key (either hex2bin or base64). Defaults to hex2bin.',
		'-show'   => 'Shows the generated key in the terminal instead of storing in the `.env` file.',
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
		$prefix = $params['prefix'] ?? CLI::getOption('prefix');
		if (in_array($prefix, [null, true], true))
		{
			$prefix = 'hex2bin';
		}
		elseif (! in_array($prefix, ['hex2bin', 'base64'], true))
		{
			// @codeCoverageIgnoreStart
			$prefix = CLI::prompt('Please provide a valid prefix to use.', ['hex2bin', 'base64'], 'required');
			// @codeCoverageIgnoreEnd
		}

		$length = $params['length'] ?? CLI::getOption('length');
		if (in_array($length, [null, true], true))
		{
			$length = 32;
		}

		$encodedKey = $this->generateRandomKey($prefix, $length);

		if (array_key_exists('show', $params) || (bool) CLI::getOption('show'))
		{
			CLI::write($encodedKey, 'yellow');
			CLI::newLine();
			return;
		}

		if (! $this->setNewEncryptionKey($encodedKey, $params))
		{
			CLI::write('Error in setting new encryption key to .env file.', 'light_gray', 'red');
			CLI::newLine();
			return;
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
	 * @param string  $prefix
	 * @param integer $length
	 *
	 * @return string
	 */
	protected function generateRandomKey(string $prefix, int $length): string
	{
		$key = Encryption::createKey($length);

		if ($prefix === 'hex2bin')
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

	/**
	 * Checks whether to overwrite existing encryption key.
	 *
	 * @param array $params
	 *
	 * @return boolean
	 */
	protected function confirmOverwrite(array $params): bool
	{
		return (array_key_exists('force', $params) || CLI::getOption('force')) || CLI::prompt('Overwrite existing key?', ['n', 'y']) === 'y';
	}

	/**
	 * Writes the new encryption key to .env file.
	 *
	 * @param string $oldKey
	 * @param string $newKey
	 *
	 * @return boolean
	 */
	protected function writeNewEncryptionKeyToFile(string $oldKey, string $newKey): bool
	{
		$baseEnv = ROOTPATH . 'env';
		$envFile = ROOTPATH . '.env';

		if (! file_exists($envFile))
		{
			if (! file_exists($baseEnv))
			{
				CLI::write('Both default shipped `env` file and custom `.env` are missing.', 'yellow');
				CLI::write('Here\'s your new key instead: ' . CLI::color($newKey, 'yellow'));
				CLI::newLine();
				return false;
			}

			copy($baseEnv, $envFile);
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
