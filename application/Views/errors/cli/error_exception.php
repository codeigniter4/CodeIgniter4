<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

An uncaught Exception was encountered

Type:        <?= get_class($exception), "\n"; ?>
Message:     <?= $message, "\n"; ?>
Filename:    <?= $exception->getFile(), "\n"; ?>
Line Number: <?= $exception->getLine(); ?>

<?php if (defined('SHOW_DEBUG_BACKTRACE') && SHOW_DEBUG_BACKTRACE === TRUE): ?>

	Backtrace:
	<?php foreach ($exception->getTrace() as $error): ?>
		<?php if (isset($error['file'])): ?>
<?= trim('-'. $error['line'] .' - '. $error['file'] .'::'. $error['function']) ."\n" ?>
		<?php endif ?>
	<?php endforeach ?>

<?php endif ?>
