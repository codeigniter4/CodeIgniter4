<div class="admin-box backup">
    <?php if (validation_errors()) : ?>
    <div class="alert alert-block alert-error fade in">
        <a class="close" data-dismiss="alert">&times;</a>
        <h4 class='alert-heading'><?php echo lang('database_validation_errors_heading'); ?></h4>
        <p><?php echo validation_errors(); ?></p>
    </div>
    <?php
    endif;
    if (empty($tables) || ! is_array($tables)) :
    ?>
    <div class="alert alert-error">
        <p><?php echo lang('database_backup_no_tables'); ?></p>
    </div>
    <?php
    else :
        echo form_open(SITE_AREA . '/developer/database/backup', 'class="form-horizontal"');
    ?>
        <fieldset>
            <?php foreach ($tables as $table) : ?>
            <input type="hidden" name="tables[]" value="<?php e($table); ?>" />
            <?php endforeach; ?>
            <div class="alert alert-info">
                <p><?php echo lang('database_backup_warning'); ?></p>
            </div>
            <div class="control-group<?php echo form_error('file_name') ? ' error' : ''; ?>">
                <label for="file_name" class="control-label"><?php echo lang('database_filename'); ?></label>
                <div class="controls">
                    <input type="text" name="file_name" id="file_name" value="<?php echo set_value('file_name', empty($file) ? '' : $file); ?>" />
                    <span class="help-inline"><?php echo form_error('file_name'); ?></span>
                </div>
            </div>
            <div class="control-group<?php echo form_error('drop_tables') ? ' error' : ''; ?>">
                <label for="drop_tables" class="control-label"><?php echo lang('database_drop_question'); ?></label>
                <div class="controls">
                    <select name="drop_tables" id="drop_tables">
                        <option value="0" <?php echo set_select('drop_tables', '0'); ?>><?php echo lang('bf_no'); ?></option>
                        <option value="1" <?php echo set_select('drop_tables', '1'); ?>><?php echo lang('bf_yes'); ?></option>
                    </select>
                    <span class="help-inline"><?php echo form_error('drop_tables'); ?></span>
                </div>
            </div>
            <div class="control-group<?php echo form_error('add_inserts') ? ' error' : ''; ?>">
                <label for="add_inserts" class="control-label"><?php echo lang('database_insert_question'); ?></label>
                <div class="controls">
                    <select name="add_inserts" id="add_inserts">
                        <option value="0" <?php echo set_select('add_inserts', '0'); ?>><?php echo lang('bf_no'); ?></option>
                        <option value="1" <?php echo set_select('add_inserts', '1', true); ?>><?php echo lang('bf_yes'); ?></option>
                    </select>
                    <span class="help-inline"><?php echo form_error('add_inserts'); ?></span>
                </div>
            </div>
            <div class="control-group<?php echo form_error('file_type') ? ' error' : ''; ?>">
                <label for="file_type" class="control-label"><?php echo lang('database_compress_question'); ?></label>
                <div class="controls">
                    <select name="file_type" id="file_type">
                        <option value="txt" <?php echo set_select('file_type', 'txt', true); ?>><?php echo lang('bf_none'); ?></option>
                        <option value="gzip" <?php echo set_select('file_type', 'gzip'); ?>><?php echo lang('database_gzip'); ?></option>
                        <option value="zip" <?php echo set_select('file_type', 'zip'); ?>><?php echo lang('database_zip'); ?></option>
                    </select>
                    <span class="help-inline"><?php echo form_error('file_type'); ?></span>
                </div>
            </div>
            <div class="alert alert-warning">
                <?php echo lang('database_restore_note'); ?>
            </div>
            <div class="small control-group<?php echo form_error('tables') ? ' error' : ''; ?>">
                <label class='control-label' for='table_names'><?php echo lang('database_backup_tables'); ?></label>
                <div id='table_names' class='controls'>
                    <span class='input-block-level uneditable-input'><?php e(implode(', ', $tables)); ?></span>
                </div>
            </div>
        </fieldset>
        <fieldset class="form-actions">
            <button type="submit" name="backup" class="btn btn-primary"><?php echo lang('database_backup'); ?></button>
            <?php echo ' ' . lang('bf_or') . ' ' . anchor(SITE_AREA . '/developer/database', lang('bf_action_cancel')); ?>
        </fieldset>
    <?php
        echo form_close();
    endif;
    ?>
</div>