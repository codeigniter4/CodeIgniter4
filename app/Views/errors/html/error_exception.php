<?php $error_id = uniqid('error', true); ?>
<!doctype html>
<html>
<head>
	<meta charset="UTF-8">
	<meta name="robots" content="noindex">

	<title><?= htmlspecialchars($title, ENT_SUBSTITUTE, 'UTF-8') ?></title>
	<style type="text/css">
		<?= preg_replace('#[\r\n\t ]+#', ' ', file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'debug.css')) ?>
	</style>

	<script type="text/javascript">
		<?= file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'debug.js') ?>
	</script>
</head>
<body onload="init()">

	<!-- Header -->
	<div class="header">
		<div class="container">
			<h1><?= htmlspecialchars($title, ENT_SUBSTITUTE, 'UTF-8'), ($exception->getCode() ? ' #' . $exception->getCode() : '') ?></h1>
			<p>
				<?= $exception->getMessage() ?>
				<a href="https://www.google.com/search?q=<?= urlencode($title . ' ' . preg_replace('#\'.*\'|".*"#Us', '', $exception->getMessage())) ?>"
				   rel="noreferrer" target="_blank">search &rarr;</a>
			</p>
		</div>
	</div>

	<!-- Source -->
	<div class="container">
		<p><b><?= static::cleanPath($file, $line) ?></b> at line <b><?= $line ?></b></p>

		<?php if (is_file($file)) : ?>
			<div class="source">
				<?= static::highlightFile($file, $line, 15); ?>
			</div>
		<?php endif; ?>
	</div>

	<div class="container">

		<ul class="tabs" id="tabs">
			<li><a href="#backtrace">Backtrace</a></li>
				<li><a href="#server">Server</a></li>
				<li><a href="#request">Request</a></li>
				<li><a href="#response">Response</a></li>
				<li><a href="#files">Files</a></li>
				<li><a href="#memory">Memory</a></li>
			</li>
		</ul>

		<div class="tab-content">

			<!-- Backtrace -->
			<div class="content" id="backtrace">

				<ol class="trace">
				<?php foreach ($trace as $index => $row) : ?>

					<li>
						<p>
							<!-- Trace info -->
							<?php if (isset($row['file']) && is_file($row['file'])) :?>
								<?php
								if (isset($row['function']) && in_array($row['function'], ['include', 'include_once', 'require', 'require_once']))
									{
									echo $row['function'] . ' ' . static::cleanPath($row['file']);
								}
								else
									{
									echo static::cleanPath($row['file']) . ' : ' . $row['line'];
								}
								?>
							<?php else : ?>
								{PHP internal code}
							<?php endif; ?>

							<!-- Class/Method -->
							<?php if (isset($row['class'])) : ?>
								&nbsp;&nbsp;&mdash;&nbsp;&nbsp;<?= $row['class'] . $row['type'] . $row['function'] ?>
								<?php if (! empty($row['args'])) : ?>
									<?php $args_id = $error_id . 'args' . $index ?>
									( <a href="#" onclick="return toggle('<?= $args_id ?>');">arguments</a> )
									<div class="args" id="<?= $args_id ?>">
										<table cellspacing="0">

										<?php
										$params = null;
										// Reflection by name is not available for closure function
										if (substr( $row['function'], -1 ) !== '}')
										{
											$mirror = isset( $row['class'] ) ? new \ReflectionMethod( $row['class'], $row['function'] ) : new \ReflectionFunction( $row['function'] );
											$params = $mirror->getParameters();
										}
										foreach ($row['args'] as $key => $value) : ?>
											<tr>
												<td><code><?= htmlspecialchars(isset($params[$key]) ? '$' . $params[$key]->name : "#$key", ENT_SUBSTITUTE, 'UTF-8') ?></code></td>
												<td><pre><?= print_r($value, true) ?></pre></td>
											</tr>
										<?php endforeach ?>

										</table>
									</div>
								<?php else : ?>
									()
								<?php endif; ?>
							<?php endif; ?>

							<?php if (! isset($row['class']) && isset($row['function'])) : ?>
								&nbsp;&nbsp;&mdash;&nbsp;&nbsp;	<?= $row['function'] ?>()
							<?php endif; ?>
						</p>

						<!-- Source? -->
						<?php if (isset($row['file']) && is_file($row['file']) &&  isset($row['class'])) : ?>
							<div class="source">
								<?= static::highlightFile($row['file'], $row['line']) ?>
							</div>
						<?php endif; ?>
					</li>

				<?php endforeach; ?>
				</ol>

			</div>

			<!-- Server -->
			<div class="content" id="server">
				<?php foreach (['_SERVER', '_SESSION'] as $var) : ?>
					<?php if (empty($GLOBALS[$var]) || ! is_array($GLOBALS[$var]))
					{
						continue;
} ?>

					<h3>$<?= $var ?></h3>

					<table>
						<thead>
							<tr>
								<th>Key</th>
								<th>Value</th>
							</tr>
						</thead>
						<tbody>
						<?php foreach ($GLOBALS[$var] as $key => $value) : ?>
							<tr>
								<td><?= htmlspecialchars($key, ENT_IGNORE, 'UTF-8') ?></td>
								<td>
									<?php if (is_string($value)) : ?>
										<?= htmlspecialchars($value, ENT_SUBSTITUTE, 'UTF-8') ?>
									<?php else: ?>
										<?= '<pre>' . print_r($value, true) ?>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>

				<?php endforeach ?>

				<!-- Constants -->
				<?php $constants = get_defined_constants(true); ?>
				<?php if (! empty($constants['user'])) : ?>
					<h3>Constants</h3>

					<table>
						<thead>
							<tr>
								<th>Key</th>
								<th>Value</th>
							</tr>
						</thead>
						<tbody>
						<?php foreach ($constants['user'] as $key => $value) : ?>
							<tr>
								<td><?= htmlspecialchars($key, ENT_IGNORE, 'UTF-8') ?></td>
								<td>
									<?php if (! is_array($value) && ! is_object($value)) : ?>
										<?= htmlspecialchars($value, ENT_SUBSTITUTE, 'UTF-8') ?>
									<?php else: ?>
										<?= '<pre>' . print_r($value, true) ?>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>
			</div>

			<!-- Request -->
			<div class="content" id="request">
				<?php $request = \Config\Services::request(); ?>

				<table>
					<tbody>
						<tr>
							<td style="width: 10em">Path</td>
							<td><?= $request->uri ?></td>
						</tr>
						<tr>
							<td>HTTP Method</td>
							<td><?= $request->getMethod(true) ?></td>
						</tr>
						<tr>
							<td>IP Address</td>
							<td><?= $request->getIPAddress() ?></td>
						</tr>
						<tr>
							<td style="width: 10em">Is AJAX Request?</td>
							<td><?= $request->isAJAX() ? 'yes' : 'no' ?></td>
						</tr>
						<tr>
							<td>Is CLI Request?</td>
							<td><?= $request->isCLI() ? 'yes' : 'no' ?></td>
						</tr>
						<tr>
							<td>Is Secure Request?</td>
							<td><?= $request->isSecure() ? 'yes' : 'no' ?></td>
						</tr>
						<tr>
							<td>User Agent</td>
							<td><?= $request->getUserAgent()->getAgentString() ?></td>
						</tr>

					</tbody>
				</table>


				<?php $empty = true; ?>
				<?php foreach (['_GET', '_POST', '_COOKIE'] as $var) : ?>
					<?php if (empty($GLOBALS[$var]) || ! is_array($GLOBALS[$var]))
					{
						continue;
} ?>

					<?php $empty = false; ?>

					<h3>$<?= $var ?></h3>

					<table style="width: 100%">
						<thead>
							<tr>
								<th>Key</th>
								<th>Value</th>
							</tr>
						</thead>
						<tbody>
						<?php foreach ($GLOBALS[$var] as $key => $value) : ?>
							<tr>
								<td><?= htmlspecialchars($key, ENT_IGNORE, 'UTF-8') ?></td>
								<td>
									<?php if (! is_array($value) && ! is_object($value)) : ?>
										<?= htmlspecialchars($value, ENT_SUBSTITUTE, 'UTF-8') ?>
									<?php else: ?>
										<?= '<pre>' . print_r($value, true) ?>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>

				<?php endforeach ?>

				<?php if ($empty) : ?>

					<div class="alert">
						No $_GET, $_POST, or $_COOKIE Information to show.
					</div>

				<?php endif; ?>

				<?php $headers = $request->getHeaders(); ?>
				<?php if (! empty($headers)) : ?>

					<h3>Headers</h3>

					<table>
						<thead>
							<tr>
								<th>Header</th>
								<th>Value</th>
							</tr>
						</thead>
						<tbody>
						<?php foreach ($headers as $name => $value) : ?>
							<?php if (empty($value))
							{
								continue;
} ?>
							<?php if (! is_array($value))
							{
								$value = [$value];
} ?>
							<?php foreach ($value as $h) : ?>
								<tr>
									<td><?= esc($h->getName(), 'html') ?></td>
									<td><?= esc($h->getValueLine(), 'html') ?></td>
								</tr>
							<?php endforeach; ?>
						<?php endforeach; ?>
						</tbody>
					</table>

				<?php endif; ?>
			</div>

			<!-- Response -->
			<?php
				$response = \Config\Services::response();
				$response->setStatusCode(http_response_code());
			?>
			<div class="content" id="response">
				<table>
					<tr>
						<td style="width: 15em">Response Status</td>
						<td><?= $response->getStatusCode() . ' - ' . $response->getReason() ?></td>
					</tr>
				</table>

				<?php $headers = $response->getHeaders(); ?>
				<?php if (! empty($headers)) : ?>
					<?php natsort($headers) ?>

					<h3>Headers</h3>

					<table>
						<thead>
							<tr>
								<th>Header</th>
								<th>Value</th>
							</tr>
						</thead>
						<tbody>
						<?php foreach ($headers as $name => $value) : ?>
							<tr>
								<td><?= esc($name, 'html') ?></td>
								<td><?= esc($response->getHeaderLine($name), 'html') ?></td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>

				<?php endif; ?>
			</div>

			<!-- Files -->
			<div class="content" id="files">
				<?php $files = get_included_files(); ?>

				<ol>
				<?php foreach ($files as $file) :?>
					<li><?= htmlspecialchars( static::cleanPath($file), ENT_SUBSTITUTE, 'UTF-8') ?></li>
				<?php endforeach ?>
				</ol>
			</div>

			<!-- Memory -->
			<div class="content" id="memory">

				<table>
					<tbody>
						<tr>
							<td>Memory Usage</td>
							<td><?= static::describeMemory(memory_get_usage(true)) ?></td>
						</tr>
						<tr>
							<td style="width: 12em">Peak Memory Usage:</td>
							<td><?= static::describeMemory(memory_get_peak_usage(true)) ?></td>
						</tr>
						<tr>
							<td>Memory Limit:</td>
							<td><?= ini_get('memory_limit') ?></td>
						</tr>
					</tbody>
				</table>

			</div>

		</div>  <!-- /tab-content -->

	</div> <!-- /container -->

	<div class="footer">
		<div class="container">

			<p>
				Displayed at <?= date('H:i:sa') ?> &mdash;
				PHP: <?= phpversion() ?>  &mdash;
				CodeIgniter: <?= \CodeIgniter\CodeIgniter::CI_VERSION ?>
			</p>

		</div>
	</div>

</body>
</html>
