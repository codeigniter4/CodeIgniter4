<h2>View a <?= $single_name ?></h2>

<table>
    <tbody>
    <?php foreach ($fields as $field) : ?>
    <tr>
        <td><?= $field['name'] ?></td>
        <td>@= $item-><?= $field['name'] ?> ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
