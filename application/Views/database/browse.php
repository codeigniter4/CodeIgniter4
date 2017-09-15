<div class="alert alert-info">
    <h4 class='alert-heading'><?php e(lang('database_sql_query')); ?>:</h4>
    <p><?php e($query); ?></p>
</div>
<?php if (empty($num_rows) || empty($rows) || ! is_array($rows)) : ?>
<div class="alert alert-warning">
    <?php e(lang('database_no_rows')); ?>
</div>
<?php else : ?>
<p><?php echo e(sprintf(lang('database_total_results'), $num_rows)); ?></p>
<div class="admin-box">
    <table class="table table-striped">
        <thead>
            <tr>
                <?php foreach ($rows[0] as $field => $value) : ?>
                <th><?php e($field); ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row) : ?>
            <tr>
                <?php foreach ($row as $key => $value) : ?>
                <td><?php e($value); ?></td>
                <?php endforeach; ?>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php
endif;
