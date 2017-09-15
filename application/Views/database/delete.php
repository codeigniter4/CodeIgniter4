<?php
/**
 * @todo Is this still in use?
 */
echo form_open($this->uri->uri_string());
    if ( ! empty($files) && is_array($files)) :
        foreach ($files as $file) :
?>
	<input type="hidden" name="files[]" value="<?php echo $file; ?>" />
		<?php endforeach; ?>
    <p><strong><?php echo lang('database_backup_delete_confirm'); ?></strong></p>
		<ul>
		<?php foreach($files as $file) : ?>
        <li><?php echo $file; ?></li>
		<?php endforeach; ?>
		</ul>
	<?php endif; ?>
    <fieldset class="form-actions">
		<input type="submit" name="delete" class="btn btn-danger" value="<?php echo lang('bf_action_delete'); ?> <?php echo lang('bf_files'); ?>" />
		<?php echo ' ' . lang('bf_or') . ' '; ?>
        <a href="<?php echo site_url(SITE_AREA . '/developer/database/backups'); ?>"><?php echo lang('bf_action_cancel'); ?></a>
	</fieldset>
<?php echo form_close();