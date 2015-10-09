<!doctype html>
<html>
<head>
	<meta charset="UTF-8">
	<meta name="robots" content="noindex">

	<title><?= htmlspecialchars($title, ENT_SUBSTITUTE, 'UTF-8') ?></title>
	<style type="text/css">
		<?= preg_replace('#[\r\n\t ]+#', ' ', file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'debug.css')) ?>
	</style>
</head>
<body>

	<!-- Header -->
	<div class="header">
		<div class="container">
			<h1><?= htmlspecialchars($title, ENT_SUBSTITUTE, 'UTF-8'), ($exception->getCode() ? ' #'.$exception->getCode() : '') ?></h1>
			<p>
				<?= htmlspecialchars($exception->getMessage(), ENT_SUBSTITUTE) ?>
				<a href="https://www.google.com/search?q=<?= urlencode($title.' '.preg_replace('#\'.*\'|".*"#Us', '', $exception->getMessage())) ?>"
				   rel="noreferrer" target="_blank">search &rarr;</a>
			</p>
		</div>
	</div>

	<!-- Source -->
	<div class="container">
		<p><b><?= \CodeIgniter\Core\Exceptions::cleanPath($file, $line) ?></b> at line <b><?= $line ?></b></p>

		<?php if (is_file($file)) : ?>
			<div class="source">
				<?= self::highlightFile($file, $line, 15); ?>
			</div>
		<?php endif; ?>
	</div>

</body>
</html>