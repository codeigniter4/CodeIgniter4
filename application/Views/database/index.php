<div class="admin-box database">
    <?php if (empty($tables) || ! is_array($tables)) : ?>
    <div class="notification info">
        <p><?php echo lang('database_no_tables'); ?></p>
    </div>
	<?php 
    else :
		echo form_open(SITE_AREA . '/developer/database/'); 
	?>
		<table class="table table-striped">
			<thead>
				<tr>
					<th class="column-check"><input class="check-all" type="checkbox" /></th>
                    <th><?php echo lang('database_table_name'); ?></th>
                    <th class='records'><?php echo lang('database_num_records'); ?></th>
                    <th><?php echo lang('database_data_size'); ?></th>
                    <th><?php echo lang('database_index_size'); ?></th>
                    <th><?php echo lang('database_data_free'); ?></th>
                    <th><?php echo lang('database_engine'); ?></th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="7">
                        <label for='database-action'><?php echo lang('bf_with_selected'); ?>:</label>
                        <select name="action" id='database-action' class="span2">
                            <option value="backup"><?php echo lang('database_backup'); ?></option>
                            <option value="repair"><?php echo lang('database_repair'); ?></option>
                            <option value="optimize"><?php echo lang('database_optimize'); ?></option>
							<option>------</option>
                            <option value="drop"><?php echo lang('database_drop'); ?></option>
						</select>
                        <input type="submit" value="<?php echo lang('database_apply')?>" class="btn btn-primary" />
					</td>
				</tr>
			</tfoot>
			<tbody>
				<?php foreach ($tables as $table) : ?>
				<tr>
                    <td class="column-check"><input type="checkbox" value="<?php e($table->Name); ?>" name="checked[]" /></td>
                    <td><a href="<?php e(site_url(SITE_AREA . "/developer/database/browse/{$table->Name}")); ?>"><?php e($table->Name); ?></a></td>
					<td class='records'><?php echo $table->Rows; ?></td>
                    <td><?php e(is_numeric($table->Data_length) ? byte_format($table->Data_length) : $table->Data_length); ?></td>
                    <td><?php e(is_numeric($table->Index_length) ? byte_format($table->Index_length) : $table->Index_length); ?></td>
                    <td><?php e(is_numeric($table->Data_free) ? byte_format($table->Data_free) : $table->Data_free); ?></td>
                    <td><?php e($table->Engine); ?></td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php 
		echo form_close();
    endif;
	 ?>
</div>