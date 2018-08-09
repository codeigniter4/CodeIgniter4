<style type="text/css">
	<?= preg_replace('#[\r\n\t ]+#', ' ', file_get_contents(__DIR__.'/toolbar.css')) ?>
</style>

<script id="toolbar_js" type="text/javascript">
	<?= file_get_contents(__DIR__.'/toolbar.js') ?>
</script>
<div id="debug-icon" class="debug-bar-ndisplay">
	<a id="debug-icon-link" href="javascript:void(0)">
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
		<span id="toolbar-position"><a href="javascript: void(0)">&#8597;</a></span>
		<span class="ci-label">
			<a href="javascript: void(0)" data-tab="ci-timeline">
				<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAD7SURBVEhLY6ArSEtLK09NTbWHcvGC9PR0BaDaQiAdUl9fzwQVxg+AFvwHamqHcnGCpKQkeaDa9yD1UD09UCn8AKaBWJySkmIApFehi0ONwwRQBceBLurAh4FqFoHUAtkrgPgREN+ByYEw1DhMANVEMIhAYQ5U1wtU/wmILwLZRlAp/IBYC8gGw88CaFj3A/FnIL4ETDXGUCnyANSC/UC6HIpnQMXAqQXIvo0khxNDjcMEQEmU9AzDuNI7Lgw1DhOAJIEuhQcRKMcC+e+QNHdDpcgD6BaAANSSQqBcENFlDi6AzQKqgkFlwWhxjVI8o2OgmkFaXI8CTMDAAAAxd1O4FzLMaAAAAABJRU5ErkJggg==">
				<span class="hide-sm"><?= $totalTime ?> ms &nbsp; <?= $totalMemory ?> MB</span>
			</a>
		</span>

		<?php foreach ($collectors as $c) : ?>
			<?php if (! $c['isEmpty'] && ($c['hasTabContent'] || $c['hasLabel'])) : ?>
				<span class="ci-label">
					<a href="javascript: void(0)" data-tab="ci-<?= $c['titleSafe'] ?>" >
						<img src="<?= $c['icon'] ?>">
						<span class="hide-sm">
							<?= $c['title'] ?>
							<?php if (! is_null($c['badgeValue'])) : ?>
								<span class="badge"><?= $c['badgeValue'] ?></span>
							<?php endif ?>
						</span>
					</a>
				</span>
			<?php endif ?>
		<?php endforeach ?>

		<span class="ci-label">
			<a href="javascript: void(0)" data-tab="ci-vars">
				<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAACLSURBVEhLYxgFJIHU1NSraWlp/6H4T0pKSjRUijoAyXAwBlrYDpViAFpmARQrJwZDtWACoCROC4D8CnR5XBiqBRMADfyNprgRKkUdAApzoCUdUNwE5MtApYYIALp6NBWBMVQLJgAaOJqK8AOgq+mSio6DggjEBtLUT0UwQ5HZIADkj6aiUTAggIEBANAEDa/lkCRlAAAAAElFTkSuQmCC">
				<span class="hide-sm">Vars</span>
			</a>
		</span>

		<h1>
			<span class="ci-label">
				<a href="javascript: void(0)" data-tab="ci-config" >
					<svg version="1.0" xmlns="http://www.w3.org/2000/svg"
						width="18.60px" height="24.0px" viewBox="0 0 18.60 28.000000"
						preserveAspectRatio="xMidYMid meet">
						<g transform="translate(0.000000,28.000000) scale(0.010000,-0.010000)" fill="#dd4814" stroke="none">
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
					<?= $CI_VERSION ?>
				</a>
			</span>
		</h1>

		<!-- Open/Close Toggle -->
		<a id="debug-bar-link" href="javascript:void(0)" title="Open/Close">
			<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAEPSURBVEhL7ZVLDoJAEEThRuoGDwSEG+jCuFU34s3AK3APP1VDDSGMqI1xx0s6M/2rnlHEaMZElmWrPM+vsDvsYbQ7+us0TReSC2EBrEHxCevRYuppYLXkQpC8sVCuGfTvqSE3hFdFwUGuGfRvqSE35NUAfKZrbQNQm2jrMA+gOK+M+FmhDsRL5voHMA8gFGecq0JOXLWlQg7E7AMIxZnjOiZOEJ82gFCcedUE4gS56QP8yf8ywItz7e+RituKlkkDBoIOH4Nd4HZD4NsGYJ/Abn1xEVOcuZ8f0zc/tHiYmzTAwscBvDIK/veyQ9K/rnewjdF26q0kF1IUxZIFPAVW98x/a+qp8L2M/+HMhETRE6S8TxpZ7KGXAAAAAElFTkSuQmCC">
		</a>
	</div>

	<!-- Timeline -->
	<div id="ci-timeline" class="tab">
		<table class="timeline">
			<thead>
				<tr>
					<th class="debug-bar-width30">NAME</th>
					<th class="debug-bar-width10">COMPONENT</th>
					<th class="debug-bar-width10">DURATION</th>
					<?php for ($i = 0; $i < $segmentCount; $i++) : ?>
						<th><?= $i * $segmentDuration ?> ms</th>
					<?php endfor ?>
				</tr>
			</thead>
			<tbody>
				<?= self::renderTimeline($collectors, $startTime, $segmentCount, $segmentDuration, $styles) ?>
			</tbody>
		</table>
	</div>

	<!-- Collector-provided Tabs -->
	<?php foreach ($collectors as $c) : ?>
		<?php if (! $c['isEmpty']) : ?>
			<?php if  ($c['hasTabContent']) : ?>
				<div id="ci-<?= $c['titleSafe'] ?>" class="tab">
					<h2><?= $c['title'] ?> <span><?= $c['titleDetails'] ?></span></h2>

					<?= $parser->setData($c['display'])->render("_{$c['titleSafe']}.tpl") ?>
				</div>
			<?php endif ?>
		<?php endif ?>
	<?php endforeach ?>

	<!-- In & Out -->
	<div id="ci-vars" class="tab">

		<!-- VarData from Collectors -->
		<?php if(isset($vars['varData'])) : ?>
			<?php foreach ($vars['varData'] as $heading => $items) : ?>

				<a href="javascript:void(0)" onclick="ciDebugBar.toggleDataTable('<?= strtolower(str_replace(' ', '-', $heading)) ?>'); return false;">
					<h2><?= $heading ?></h2>
				</a>

				<?php if (is_array($items)) : ?>

					<table id="<?= strtolower(str_replace(' ', '-', $heading.'_table')) ?>">
						<tbody>
						<?php foreach ($items as $key => $value) : ?>
							<tr>
								<td><?= $key ?></td>
								<td><?= $value ?></td>
							</tr>
						<?php endforeach ?>
						</tbody>
					</table>

				<?php else: ?>
					<p class="muted">No data to display.</p>
				<?php endif ?>
			<?php endforeach ?>
		<?php endif ?>

		<!-- Session -->
		<a href="javascript:void(0)" onclick="ciDebugBar.toggleDataTable('session'); return false;">
			<h2>Session User Data</h2>
		</a>

		<?php if (isset($vars['session'])) : ?>
			<?php if (! empty($vars['session'])) : ?>
				<table id="session_table">
					<tbody>
					<?php foreach ($vars['session'] as $key => $value) : ?>
						<tr>
							<td><?= $key ?></td>
							<td><?= $value ?></td>
						</tr>
					<?php endforeach ?>
					</tbody>
				</table>
			<?php else : ?>
				<p class="muted">No data to display.</p>
			<?php endif ?>
		<?php else : ?>
			<p class="muted">Session doesn't seem to be active.</p>
		<?php endif ?>

		<h2>Request <span>( <?= $vars['request'] ?> )</span></h2>

		<?php if (isset($vars['get']) && $get = $vars['get']) : ?>
			<a href="javascript:void(0)" onclick="ciDebugBar.toggleDataTable('get'); return false;">
				<h3>$_GET</h3>
			</a>

			<table id="get_table">
				<tbody>
				<?php foreach ($get as $name => $value) : ?>
					<tr>
						<td><?= $name ?></td>
						<td><?= $value ?></td>
					</tr>
				<?php endforeach ?>
				</tbody>
			</table>
		<?php endif ?>

		<?php if (isset($vars['post']) && $post = $vars['post']) : ?>
			<a href="javascript:void(0)" onclick="ciDebugBar.toggleDataTable('post'); return false;">
				<h3>$_POST</h3>
			</a>

			<table id="post_table">
				<tbody>
				<?php foreach ($post as $name => $value) : ?>
					<tr>
						<td><?= $name ?></td>
						<td><?= $value ?></td>
					</tr>
				<?php endforeach ?>
				</tbody>
			</table>
		<?php endif ?>

		<?php if (isset($vars['headers']) && $headers = $vars['headers']) : ?>
			<a href="javascript:void(0)" onclick="ciDebugBar.toggleDataTable('request_headers'); return false;">
				<h3>Headers</h3>
			</a>

			<table id="request_headers_table">
				<tbody>
				<?php foreach ($headers as $header => $value) : ?>
						<tr>
							<td><?= $header ?></td>
							<td><?= $value ?></td>
						</tr>
				<?php endforeach ?>
				</tbody>
			</table>
		<?php endif ?>

		<?php if (isset($vars['cookies']) && $cookies = $vars['cookies']) : ?>
			<a href="javascript:void(0)" onclick="ciDebugBar.toggleDataTable('cookie'); return false;">
				<h3>Cookies</h3>
			</a>

			<table id="cookie_table">
				<tbody>
				<?php foreach ($cookies as $name => $value) : ?>
					<tr>
						<td><?= $name ?></td>
						<td><?= is_array($value) ? var_dump($value) : $value ?></td>
					</tr>
				<?php endforeach ?>
				</tbody>
			</table>
		<?php endif ?>

		<h2>Response <span>( <?= $vars['response']['statusCode'].' - '. $vars['response']['reason'] ?> )</span></h2>

		<?php if (isset($vars['headers']) && $headers = $vars['headers']) : ?>
			<a href="javascript:void(0)" onclick="ciDebugBar.toggleDataTable('response_headers'); return false;">
				<h3>Headers</h3>
			</a>

			<table id="response_headers_table">
				<tbody>
				<?php foreach ($headers as $header => $value) : ?>
					<tr>
						<td><?= $header ?></td>
						<td><?= $value ?></td>
					</tr>
				<?php endforeach ?>
				</tbody>
			</table>
		<?php endif ?>
	</div>

	<!-- Config Values -->
	<div id="ci-config" class="tab">
		<h2>System Configuration</h2>

		<?= $parser->setData($config)->render('_config.tpl') ?>
	</div>
</div>
<style type="text/css">
	<?php foreach( $styles as $name => $style ) : ?>
	.<?= $name ?> {
	<?= $style ?>
	}
	<?php endforeach ?>
</style>
