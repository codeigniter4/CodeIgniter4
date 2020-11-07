<@php

namespace {namespace};

use CodeIgniter\CLI\CLI;
{useStatement}

class {class} {extends}
{
	{commandGroup}
	/**
	 * The Command's name
	 *
	 * @var string
	 */
	protected $name = '{commandName}';

	/**
	 * The Command's short description
	 *
	 * @var string
	 */
	protected $description = '';

	/**
	 * The Command's usage
	 *
	 * @var string
	 */
	protected $usage = '{commandName} [arguments] [options]';

	/**
	 * The Command's arguments.
	 *
	 * @var array
	 */
	protected $arguments = [];

	/**
	 * The Command's options.
	 *
	 * @var array
	 */
	protected $options = [];
	{commandAbstractMethodsToImplement}
}
