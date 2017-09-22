<!doctype html>
<html>
    <body>
        <h2 style="margin-top:0px">Categories Read</h2>
        <table class="table">
            <tr><td>Name</td><td><?= $name; ?></td></tr>
            <tr><td>Date</td><td><?= $date; ?></td></tr>
            <tr><td></td><td><a href="<?= base_url($controllerPath) ?>" class="btn btn-default">Cancel</a></td></tr>
        </table>
    </body>
</html>