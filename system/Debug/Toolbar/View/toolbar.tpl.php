<style type="text/css">
	<?= preg_replace('#[\r\n\t ]+#', ' ', file_get_contents(__DIR__.'/toolbar.css')) ?>
</style>

<script type="text/javascript">
	<?= file_get_contents(__DIR__.'/toolbar.js') ?>
</script>

<div id="debug-bar">
	<div class="toolbar">
		<h1>Debug Bar</h1>

		<span><?= $totalTime ?> ms</span>
		<span><?= $totalMemory ?> MB</span>
		<span class="ci-label"><a href="javascript: void(0)" data-tab="ci-timeline">Timeline</a></span>
		<?php foreach ($this->collectors as $c) : ?>
			<?php if ($c->hasTabContent()) : ?>
				<span class="ci-label"><a href="javascript: void(0)" data-tab="ci-<?= esc($c->getTitle(true)) ?>"><?= esc($c->getTitle()) ?></a></span>
			<?php endif; ?>
		<?php endforeach; ?>
		<span class="ci-label"><a href="javascript: void(0)" data-tab="ci-request">Request</a></span>
		<span class="ci-label"><a href="javascript: void(0)" data-tab="ci-response">Response</a></span>
	</div>

	<!-- Timeline -->
	<div id="ci-timeline" class="tab">
		<table class="timeline">
			<thead>
				<tr>
					<th style="width: 30%">NAME</th>
					<th style="width: 10%">COMPONENT</th>
					<th style="width: 10%;">DURATION</th>
					<?php for ($i=0; $i < $segmentCount; $i++) : ?>
						<th><?= $i * $segmentDuration ?> ms</th>
					<?php endfor; ?>
				</tr>
			</thead>
			<tbody>
				<?= $this->renderTimeline($segmentCount, $segmentDuration, $totalTime) ?>
			</tbody>
		</table>
	</div>

	<!-- Collector-provided Tabs -->
	<?php foreach ($this->collectors as $c) : ?>
		<?php if  ($c->hasTabContent()) : ?>
			<div id="ci-<?= esc($c->getTitle(true)) ?>" class="tab">
				<h2><?= esc($c->getTitle()) ?> <span><?= esc($c->getTitleDetails()) ?></span></h2>

				<?= $c->display() ?>
			</div>
		<?php endif ?>
	<?php endforeach ?>

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
		<h2>Response <span>( <?= $response->getStatusCode().' - '. esc($response->getReason()) ?> )</span></h2>

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