<style type="text/css">
	<?= preg_replace('#[\r\n\t ]+#', ' ', file_get_contents(__DIR__.'/toolbar.css')) ?>
</style>

<script type="text/javascript">
	<?= file_get_contents(__DIR__.'/toolbar.js') ?>
</script>

<div id="debug-bar">
	<div class="toolbar">
		<h1>Debug Bar</h1>

		<span class="ci-label"><?= $totalTime ?> ms</span>
		<span class="ci-label"><?= $totalMemory ?> MB</span>
		<span class="ci-label"><a href="javascript: void(0)" data-tab="ci-timeline">Timeline</a></span>
		<span class="ci-label"><a href="javascript: void(0)" data-tab="ci-request">Request</a></span>
		<span class="ci-label"><a href="javascript: void(0)" data-tab="ci-response">Response</a></span>
	</div>

	<!-- Timeline -->
	<div id="ci-timeline" class="tab">

	</div>

	<!-- Request -->
	<div id="ci-request" class="tab">
		<h2>Request <span>( <?= ($request->isSecure() ? 'HTTPS' : 'HTTP').'/'.$request->getProtocolVersion() ?> )</span></h2>

		<?php if ($get = $request->getGet()) : ?>
			<h3>$_GET</h3>

			<table>
				<tbody>
				<?php foreach ($get as $name => $value) : ?>
					<tr>
						<td><?= esc($name) ?></td>
						<td><?= esc($value) ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif ?>

		<?php if ($post = $request->getPost()) : ?>
			<h3>$_POST</h3>

			<table>
				<tbody>
				<?php foreach ($post as $name => $value) : ?>
					<tr>
						<td><?= esc($name) ?></td>
						<td><?= esc($value) ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif ?>

		<?php if ($headers = $request->getHeaders()) : ?>
			<h3>Headers</h3>

			<table>
				<tbody>
				<?php foreach ($headers as $header => $value) : ?>
					<tr>
						<td><?= esc($header) ?></td>
						<td><?= esc($request->getHeaderLine($header)) ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif ?>

		<?php if ($get = $request->getCookie()) : ?>
			<h3>Cookies</h3>

			<table>
				<tbody>
				<?php foreach ($get as $name => $value) : ?>
					<tr>
						<td><?= esc($name) ?></td>
						<td><?= esc($value) ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif ?>
	</div>

	<!-- Response -->
	<div id="ci-response" class="tab">
		<h2>Response</h2>

		<?php if ($headers = $response->getHeaders()) : ?>
			<h3>Headers</h3>

			<table>
				<tbody>
				<?php foreach ($headers as $header => $value) : ?>
					<tr>
						<td><?= esc($header) ?></td>
						<td><?= esc($response->getHeaderLine($header)) ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>
</div>

<script>
	ciDebugBar.init();
</script>