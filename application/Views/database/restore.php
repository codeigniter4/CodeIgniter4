<div class='admin-box restore'>
    <?php if (empty($results)) : ?>
    <h3><?php echo sprintf(lang('database_restore_file'), $filename); ?></h3>
    <div class="alert alert-warning">
        <?php echo lang('database_restore_attention'); ?>
	</div>
	<?php echo form_open($this->uri->uri_string()); ?>
        <input type="hidden" name="filename" value="<?php echo $filename; ?>" />
        <fieldset class="form-actions">
            <input type="submit" name="restore" class="btn btn-primary" value="<?php echo lang('database_restore'); ?>" />
            <?php echo ' ' . lang('bf_or') . ' ' . anchor(SITE_AREA . '/developer/database/backups', lang('bf_action_cancel')); ?>
        </fieldset>
    <?php
        echo form_close();
    else :
    ?>
    <h3><?php echo lang('database_restore_results'); ?></h3>
    <div class='backups-link'>
        <?php echo anchor(SITE_AREA . '/developer/database/backups', lang('database_back_to_tools')); ?>
			</div>
    <div class="content-box">
        <p><?php echo $results; ?></p>
		</div>
    <div class='backups-link'>
        <?php echo anchor(SITE_AREA . '/developer/database/backups', lang('database_back_to_tools')); ?>
		</div>
    <?php endif; ?>
</div>