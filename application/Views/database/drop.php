<div class='admin-box drop-table'>
    <h3><?php echo lang('database_drop_title'); ?></h3>
    <?php if (empty($tables) || ! is_array($tables)) : ?>
    <div class="alert alert-error">
        <?php echo lang('database_drop_none'); ?>
    </div>
    <?php
    else :
        echo form_open(SITE_AREA . '/developer/database/drop');
    ?>
        <h4><?php echo lang('database_drop_confirm'); ?></h4>
        <ul>
            <?php foreach ($tables as $table) : ?>
            <li><?php e($table); ?>
                <input type="hidden" name="tables[]" value="<?php e($table); ?>" />
            </li>
            <?php endforeach; ?>
        </ul>
        <div class="alert alert-warning">
            <?php echo lang('database_drop_attention'); ?>
        </div>
        <fieldset class="form-actions">
            <button type="submit" name="drop" class="btn btn-danger"><?php e(lang('database_drop_button')); ?></button>
            <?php echo ' ' . lang('bf_or') . ' ' . anchor(SITE_AREA . '/developer/database', lang('bf_action_cancel')); ?>
        </fieldset>
    <?php
        echo form_close();
    endif;
    ?>
</div>