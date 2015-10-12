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

	<div class="container">

		<ul class="tabs">
			<li>
				<a href="#backtrace" class="active">Backtrace</a>
				<a href="#server">Server</a>
				<a href="#request">Request</a>
				<a href="#response">Response</a>
				<a href="#files">Files</a>
				<a href="#memory">Memory</a>
			</li>
		</ul>

		<div class="tab-content">

			<!-- Backtrace -->
			<div class="content active">

				<ol class="trace">
				<?php foreach ($trace as $row) : ?>

					<li>
						<p>
							<!-- Trace info -->
							<?php if (isset($row['file']) && is_file($row['file'])) :?>
								<?= self::cleanPath($row['file']).' : '.$row['line'] ?>
							<?php else : ?>
								{PHP internal code} : <?= $row['line'] ?>
							<?php endif; ?>

							<!-- Class/Method -->
							<?php if (isset($row['class'])) : ?>
								&nbsp;&nbsp;&mdash;&nbsp;&nbsp;<?= $row['class'].$row['type'].$row['function'] ?>()
							<?php endif; ?>
						</p>

						<!-- Source? -->
						<?php if (isset($row['file']) && is_file($row['file']) &&  isset($row['class'])) : ?>
							<div class="source">
								<?= self::highlightFile($row['file'], $row['line']) ?>
							</div>
						<?php endif; ?>
					</li>

				<?php endforeach; ?>
				</ol>

			</div>

			<!-- Server -->
			<div class="content">

			</div>

			<!-- Request -->
			<div class="content">

			</div>

			<!-- Response -->
			<div class="content">

			</div>

			<!-- Files -->
			<div class="content">

			</div>

			<!-- Memory -->
			<div class="content">

			</div>

		</div>  <!-- /tab-content -->

	</div> <!-- /container -->

</body>
</html>