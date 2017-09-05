<!doctype html>
<html>
    <head>
        <title>harviacode.com - codeigniter crud generator</title>
        <link rel="stylesheet" href="<?php echo base_url('assets/bootstrap/css/bootstrap.min.css') ?>"/>
        <style>
            .word-table {
                border:1px solid black !important; 
                border-collapse: collapse !important;
                width: 100%;
            }
            .word-table tr th, .word-table tr td{
                border:1px solid black !important; 
                padding: 5px 10px;
            }
        </style>
    </head>
    <body>
        <h2>Users List</h2>
        <table class="word-table" style="margin-bottom: 10px">
            <tr>
                <th>No</th>
		<th>Name</th>
		<th>Email</th>
		<th>Password</th>
		<th>Telephone</th>
		<th>Address</th>
		<th>Ip</th>
		<th>Admin</th>
		<th>Date</th>
		
            </tr><?php
            foreach ($users_data as $users)
            {
                ?>
                <tr>
		      <td><?php echo ++$start ?></td>
		      <td><?php echo $users->name ?></td>
		      <td><?php echo $users->email ?></td>
		      <td><?php echo $users->password ?></td>
		      <td><?php echo $users->telephone ?></td>
		      <td><?php echo $users->address ?></td>
		      <td><?php echo $users->ip ?></td>
		      <td><?php echo $users->admin ?></td>
		      <td><?php echo $users->date ?></td>	
                </tr>
                <?php
            }
            ?>
        </table>
    </body>
</html>