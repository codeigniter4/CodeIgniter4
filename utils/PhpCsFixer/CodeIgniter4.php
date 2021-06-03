<?php

declare(strict_types=1);

namespace Utils\PhpCsFixer;

use Nexus\CsConfig\Ruleset\AbstractRuleset;

/**
 * Defines the ruleset used for the CodeIgniter4 organization.
 *
 * @internal
 */
final class CodeIgniter4 extends AbstractRuleset
{
	public function __construct()
	{
		$this->name = 'CodeIgniter4 Revised Coding Standards';

		$this->rules = [];

		$this->requiredPHPVersion = 70300;

		$this->autoActivateIsRiskyAllowed = true;
	}
}
