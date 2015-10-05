<?php namespace Psr\Log;

/**
 * Describes log levels
 *
 * Not used within CodeIgniter, but provided as a
 * service to framework-agnostic libraries.
 */
class LogLevel
{
	const EMERGENCY = 1;
	const ALERT     = 2;
	const CRITICAL  = 3;
	const ERROR     = 4;
	const WARNING   = 5;
	const NOTICE    = 6;
	const INFO      = 7;
	const DEBUG     = 8;
}