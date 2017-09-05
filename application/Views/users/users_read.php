<div><h4>Users </h4></div>
<table class="table">
    <tr><td>Name</td><td><?php echo $name; ?></td></tr>
    <tr><td>Email</td><td><?php echo $email; ?></td></tr>
    <tr><td>Password</td><td><?php echo $password; ?></td></tr>
    <tr><td>Telephone</td><td><?php echo $telephone; ?></td></tr>
    <tr><td>Address</td><td><?php echo $address; ?></td></tr>
    <tr><td>Ip</td><td><?php echo $ip; ?></td></tr>
    <tr><td>Admin</td><td><?php echo $admin; ?></td></tr>
    <tr><td>Date</td><td><?php echo $date; ?></td></tr>
    <tr><td></td><td><a href="<?php echo site_url('admin/users') ?>" class="btn btn-warning"\>Cancel</a></td></tr>
</table>