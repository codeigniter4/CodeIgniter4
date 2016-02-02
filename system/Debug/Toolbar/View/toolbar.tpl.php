<style type="text/css">
	<?= preg_replace('#[\r\n\t ]+#', ' ', file_get_contents(__DIR__.'/toolbar.css')) ?>
</style>

<script type="text/javascript">
	<?= file_get_contents(__DIR__.'/toolbar.js') ?>
</script>

<div id="debug-bar">
	<div class="toolbar">
		<h1>Debug Bar</h1>

		<span class="label"><?= $totalTime ?>ms</span>
		<span class="label"><a href="javascript: void(0)">Timeline</a></span>
	</div>
</div>

<script>
	ciDebugBar.init();
</script>