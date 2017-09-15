<h2><?= $plural_name ?></h2>


<?= $uikit->buttonLink("New {$single_name}", "{$lower_name}/create", 'primary') ?>

<hr/>

@php if ( ! empty($rows) && is_array($rows) && count($rows) ) : ?>

    @php $headers = array_keys($rows[0]); ?>

    <table class="table">
        <thead>
        <tr>
        @php foreach ($headers as $name) : ?>
            <th>@= $name ?></th>
        @php endforeach; ?>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        @php foreach ($rows as $row) : ?>
            <tr>
                @php foreach ($headers as $key) : ?>
                    <td>@= $row[$key] ?></td>
                @php endforeach; ?>
                <td>
                    <a href="<?= $lower_name ?>/update/@= $row['id'] ?>">Edit</a> |
                    <a href="<?= $lower_name ?>/delete/@= $row['id'] ?>" onclick="return confirm('Delete this item?');">Delete</a>
                </td>
            </tr>
        @php endforeach; ?>
        </tbody>
    </table>

@php else : ?>

    <?= $uikit->notice('Unable to find any records.', 'warning'); ?>

@php endif; ?>
