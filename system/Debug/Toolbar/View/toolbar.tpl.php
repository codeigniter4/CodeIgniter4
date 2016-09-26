<style type="text/css">
	<?= preg_replace('#[\r\n\t ]+#', ' ', file_get_contents(__DIR__.'/toolbar.css')) ?>
</style>

<script type="text/javascript">
	<?= file_get_contents(__DIR__.'/toolbar.js') ?>
</script>
<div id="debug-icon" style="display:none">
    <a href="#" onclick="ciDebugBar.toggleToolbar();">
	    <svg version="1.0" xmlns="http://www.w3.org/2000/svg"
	         width="155.000000px" height="200.000000px" viewBox="0 0 155.000000 200.000000"
	         preserveAspectRatio="xMidYMid meet">
	    <g transform="translate(0.000000,200.000000) scale(0.100000,-0.100000)" fill="#dd4814" stroke="none">
	    <path d="M737 1963 c22 -79 -7 -185 -78 -290 -18 -26 -107 -122 -197 -213
	          -239 -240 -336 -371 -403 -544 -79 -206 -78 -408 5 -582 64 -134 212 -264 361
	          -314 l60 -20 -30 22 c-210 152 -229 387 -48 588 25 27 48 50 51 50 4 0 7 -27
	          7 -61 0 -57 2 -62 37 -95 30 -27 46 -34 78 -34 56 0 99 24 116 65 29 69 16
	          120 -50 205 -105 134 -117 233 -43 347 l31 48 7 -47 c13 -82 58 -129 250 -258
	          209 -141 306 -261 328 -405 11 -72 -1 -161 -31 -218 -27 -53 -112 -143 -165
	          -174 -24 -14 -43 -26 -43 -28 0 -2 24 4 53 14 241 83 427 271 482 486 19 76
	          19 202 -1 285 -35 152 -146 305 -299 412 l-70 49 -6 -33 c-8 -48 -26 -76 -59
	          -93 -45 -23 -103 -19 -138 10 -67 57 -78 146 -37 305 30 116 32 206 5 291 -27
	          89 -104 206 -162 247 -17 13 -18 12 -11 -15z"/>
	    </g>
	    </svg>
		</a>
</div>
<div id="debug-bar">
	<div class="toolbar">
		<h1><a href="#" onclick="ciDebugBar.toggleToolbar();">Debug Bar</a></h1>

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
