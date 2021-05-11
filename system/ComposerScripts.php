<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter;

use Composer\Script\Event;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use UnexpectedValueException;

/**
 * This class is used by Composer during installs and updates
 * to move files to locations within the system folder so that end-users
 * do not need to use Composer to install a package, but can simply
 * download.
 *
 * @codeCoverageIgnore
 *
 * @internal
 */
final class ComposerScripts
{
	/**
	 * Path to the ThirdParty directory.
	 *
	 * @var string
	 */
	private static $path = __DIR__ . '/ThirdParty/';

	/**
	 * Direct dependencies of CodeIgniter to copy
	 * contents to `system/ThirdParty/`.
	 *
	 * @var array<string, array<string, string>>
	 */
	private static $dependencies = [
		'kint-src' => [
			'from' => __DIR__ . '/../vendor/kint-php/kint/src/',
			'to'   => __DIR__ . '/ThirdParty/Kint/',
		],
		'kint-resources' => [
			'from' => __DIR__ . '/../vendor/kint-php/kint/resources/',
			'to'   => __DIR__ . '/ThirdParty/Kint/resources/',
		],
		'laminas-escaper' => [
			'from' => __DIR__ . '/../vendor/laminas/laminas-escaper/src/',
			'to'   => __DIR__ . '/ThirdParty/Escaper/',
		],
		'psr-cache' => [
			'from' => __DIR__ . '/../vendor/psr/cache/src/',
			'to'   => __DIR__ . '/ThirdParty/PSR/Cache/',
		],
		'psr-log' => [
			'from' => __DIR__ . '/../vendor/psr/log/Psr/Log/',
			'to'   => __DIR__ . '/ThirdParty/PSR/Log/',
		],
		'psr-simple-cache' => [
			'from' => __DIR__ . '/../vendor/psr/simple-cache/src/',
			'to'   => __DIR__ . '/ThirdParty/PSR/SimpleCache/',
		],
	];

	/**
	 * This static method is called by Composer after every update event,
	 * i.e., `composer install`, `composer update`, `composer remove`.
	 *
	 * @return void
	 */
	public static function postUpdate(Event $event)
	{
		$event->getIO()->write('> <info>Removing:</info> ' . basename(self::$path) . '/');
		self::recursiveDelete(self::$path);

		foreach (self::$dependencies as $name => $dependency)
		{
			$event->getIO()->write("> <info>Copying:</info> $name");
			self::recursiveMirror($dependency['from'], $dependency['to']);
		}

		$event->getIO()->write('> <info>Copying:</info> kint-init');
		self::copyKintInitFiles();
		self::recursiveDelete(self::$dependencies['psr-log']['to'] . 'Test/');
	}

	/**
	 * Recursively remove the contents of the previous `system/ThirdParty`.
	 *
	 * @param string $directory
	 *
	 * @throws UnexpectedValueException
	 *
	 * @return void
	 */
	private static function recursiveDelete(string $directory): void
	{
		if (! is_dir($directory))
		{
			throw new UnexpectedValueException(sprintf('Cannot recursively delete "%s" as it does not exist.', $directory));
		}

		/** @var SplFileInfo $file */
		foreach (new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator(rtrim($directory, '\\/'), FilesystemIterator::SKIP_DOTS),
			RecursiveIteratorIterator::CHILD_FIRST
		) as $file)
		{
			$path = $file->getPathname();

			if ($file->isDir())
			{
				@rmdir($path);
			}
			else
			{
				@unlink($path);
			}
		}

		// delete the top level directory
		@rmdir($directory);
	}

	/**
	 * Recursively copy the files and directories of the origin directory
	 * into the target directory, i.e. "mirror" its contents.
	 *
	 * @param string $originDir
	 * @param string $targetDir
	 *
	 * @throws UnexpectedValueException
	 *
	 * @return void
	 */
	private static function recursiveMirror(string $originDir, string $targetDir): void
	{
		$originDir = rtrim($originDir, '\\/');
		$targetDir = rtrim($targetDir, '\\/');

		if (! is_dir($originDir))
		{
			throw new UnexpectedValueException(sprintf('The origin directory "%s" was not found.', $originDir));
		}

		if (is_dir($targetDir))
		{
			throw new UnexpectedValueException(sprintf('The target directory "%s" is existing. Run %s::recursiveDelete(\'%s\') first.', $targetDir, self::class, $targetDir));
		}

		@mkdir($targetDir, 0755, true);

		$dirLen = strlen($originDir);

		/** @var SplFileInfo $file */
		foreach (new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($originDir, FilesystemIterator::SKIP_DOTS),
			RecursiveIteratorIterator::SELF_FIRST
		) as $file)
		{
			$origin = $file->getPathname();
			$target = $targetDir . substr($origin, $dirLen);

			if ($file->isDir())
			{
				@mkdir($target, 0755);
			}
			else
			{
				@copy($origin, $target);
			}
		}
	}

	/**
	 * Copy Kint's init files into `system/ThirdParty/Kint/`
	 *
	 * @return void
	 */
	private static function copyKintInitFiles(): void
	{
		$originDir = self::$dependencies['kint-src']['from'] . '../';
		$targetDir = self::$dependencies['kint-src']['to'];

		foreach (['init.php', 'init_helpers.php'] as $kintInit)
		{
			@copy($originDir . $kintInit, $targetDir . $kintInit);
		}
	}
}
