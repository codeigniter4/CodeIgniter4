<?php

$migrationsSegment = $this->uri->segment(3);
$checkSegment = $this->uri->segment(4);
$developerUrl = site_url(SITE_AREA . '/developer');

?>
<ul class="nav nav-pills">
	<li<?php echo $checkSegment == '' && $migrationsSegment != 'migrations' ? ' class="active"' : ''; ?>>
		<a href='<?php echo "{$developerUrl}/database"; ?>'><?php echo lang('database_maintenance'); ?></a>
	</li>
	<li<?php echo $checkSegment == 'backups' ? ' class="active"' : ''; ?>>
		<a href='<?php echo "{$developerUrl}/database/backups"; ?>'><?php echo lang('database_backups'); ?></a>
	</li>
	<li<?php echo $migrationsSegment == 'migrations' ? ' class="active"' : ''; ?>>
		<a href='<?php echo "{$developerUrl}/migrations"; ?>'><?php echo lang('database_migrations'); ?></a>
	</li>
</ul>