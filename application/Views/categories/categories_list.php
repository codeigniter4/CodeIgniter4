<!doctype html>
<html>
    <body>
        <h2 style="margin-top:0px">Categories </h2>
        <div class="row" style="margin-bottom: 10px">
            <div class="col-md-4">
                <?= anchor(base_url($controllerPath . '/create'), 'Create', 'class="btn btn-primary"'); ?>
            </div>
            <div class="col-md-4 text-center">
                <div style="margin-top: 8px" id="message">
                    <?= session('message') <> '' ? session('message') : ''; ?>
                </div>
            </div>
            <div class="col-md-1 text-right">
            </div>
            <div class="col-md-3 text-right">
                <form action="<?= base_url($controllerPath); ?>" class="form-inline" method="get">
                    <div class="input-group">
                        <input type="text" class="form-control" name="q" value="<?php //echo $q;    ?>">
                        <span class="input-group-btn">
                            <a href="<?= base_url($controllerPath); ?>" class="btn btn-default">Reset</a>
                            <button class="btn btn-primary" type="submit">Search</button>
                        </span>
                    </div>
                </form>
            </div>
        </div>
        <table class="table table-bordered" style="margin-bottom: 10px">
            <tr>
                <th>Name</th>
                <th>Date</th>
                <th>Action</th>
            </tr>
            <?php foreach ($categories as $category) : ?>
                <tr>
                    <td><?= $category['name'] ?></td>
                    <td><?= $category['date'] ?></td>
                    <td style="text-align:center" width="200px">
                        <?php
                        echo anchor(base_url($controllerPath . '/read/' . $category['id']), 'Read');
                        echo ' | ';
                        echo anchor(base_url($controllerPath . '/edit/' . $category['id']), 'Edit');
                        echo ' | ';
                        echo anchor(base_url($controllerPath . '/delete/' . $category['id']), 'Delete', 'onclick="javasciprt: return confirm(\'Are You Sure ?\')"');
                        ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <div class="row">
            <div class="col-md-6">
                <a href="#" class="btn btn-primary">Total Records: <?= $total_rows ?></a>
                <?= anchor(base_url($controllerPath . '/excel'), 'Excel', 'class="btn btn-primary"'); ?>
                <?= anchor(base_url($controllerPath . '/word'), 'Word', 'class="btn btn-primary"'); ?>
            </div>
            <div class="col-md-6 text-right">
                <?= $pager->links() ?>
            </div>
        </div>
    </body>
</html>