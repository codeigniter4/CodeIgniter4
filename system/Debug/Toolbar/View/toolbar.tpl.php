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
		<span class="ci-label"><a href="javascript: void(0)" data-tab="ci-vars">Vars</a></span>
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

	<!-- In & Out -->
	<div id="ci-vars" class="tab">

		<!-- VarData from Collectors -->
		<?php foreach ($varData as $heading => $items) : ?>

			<a href="#" onclick="ciDebugBar.toggleDataTable('<?= strtolower(str_replace(' ', '-', $heading)) ?>'); return false;">
				<h2><?= esc($heading) ?></h2>
			</a>

			<?php if (is_array($items)) : ?>

				<table id="<?= strtolower(str_replace(' ', '-', $heading.'_table')) ?>">
					<tbody>
					<?php foreach ($items as $key => $value) : ?>
						<tr>
							<td><?= esc($key) ?></td>
							<td>
							<?php
								if (is_string($value))
								{
									echo esc($value);
								}
								else
								{
									echo print_r($value, true);
								}
							?>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>

			<?php else: ?>
				<p class="muted">No data to display.</p>
			<?php endif; ?>
		<?php endforeach; ?>

		<!-- Session -->
		<a href="#" onclick="ciDebugBar.toggleDataTable('session'); return false;">
			<h2>Session User Data</h2>
		</a>

		<?php if (isset($_SESSION)) : ?>
			<?php if (count($_SESSION)) : ?>
				<table id="session_table">
					<tbody>
					<?php foreach ($_SESSION as $key => $value) : ?>
						<tr>
							<td><?= esc($key) ?></td>
							<td>
							<?php
								if (is_string($value))
								{
									echo esc($value);
								}
								else
								{
									echo print_r($value, true);
								}
							?>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			<?php else : ?>
				<p class="muted">No data to display.</p>
			<?php endif; ?>
		<?php else : ?>
			<p class="muted">Session doesn't seem to be active.</p>
		<?php endif; ?>

		<h2>Request <span>( <?= ($request->isSecure() ? 'HTTPS' : 'HTTP').'/'.$request->getProtocolVersion() ?> )</span></h2>

		<?php if ($get = $request->getGet()) : ?>
			<a href="#" onclick="ciDebugBar.toggleDataTable('get'); return false;">
				<h3>$_GET</h3>
			</a>

			<table id="get_table">
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
			<a href="#" onclick="ciDebugBar.toggleDataTable('post'); return false;">
				<h3>$_POST</h3>
			</a>

			<table id="post_table">
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
			<a href="#" onclick="ciDebugBar.toggleDataTable('request_headers'); return false;">
				<h3>Headers</h3>
			</a>

			<table id="request_headers_table">
				<tbody>
				<?php foreach ($headers as $header => $value) : ?>
					<?php if (empty($value)) continue; ?>
					<tr>
						<td><?= esc($value->getName()) ?></td>
						<td><?= esc($value->getValueLine()) ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif ?>

		<?php if ($get = $request->getCookie()) : ?>
			<a href="#" onclick="ciDebugBar.toggleDataTable('cookie'); return false;">
				<h3>Cookies</h3>
			</a>

			<table id="cookie_table">
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

		<h2>Response <span>( <?= $response->getStatusCode().' - '. esc($response->getReason()) ?> )</span></h2>

		<?php if ($headers = $response->getHeaders()) : ?>
			<a href="#" onclick="ciDebugBar.toggleDataTable('response_headers'); return false;">
				<h3>Headers</h3>
			</a>

			<table id="response_headers_table">
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


<script>
	ciDebugBar.init();
</script>
