<?php

namespace Config;

class Mailer
{
	/**
	 * Default "from" address.
	 *
	 * @var string|null
	 */
	public $from;

	/**
	 * Default email priority.
	 * 1 = highest, 3 = normal, 5 = lowest
	 *
	 * @var integer
	 */
	public $priority = 3;
}
