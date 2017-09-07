<div class="row bg-light lter">
    <div class="col-md-4 "><h4>Users</h4></div>
    <div class="col-md-8 text-right">
	<?= anchor(site_url('admin/users/create'),'Create', 'class="btn btn-primary"'); ?>
	<?= anchor(site_url('admin/users/excel'), 'Excel', 'class="btn btn-primary"'); ?>
	<?= anchor(site_url('admin/users/word'), 'Word', 'class="btn btn-primary"'); ?>
    </div>
</div>
    <table class="table table-responsive m-b-none text-sm display nowrap datatables" width="100%" >
        <thead>
            <th>{campo_1}</th>
	    <th>{campo_2}</th>
	    <th>Email</th>
	    <th>Password</th>
	    <th>Telephone</th>
	    <th>Address</th>
	    <th>Ip</th>
	    <th>Admin</th>
	    <th>Date</th>
	    <th>Action</th>
        </thead>
        <tbody><?php foreach ($Users as $Users) : ?>
                <tr>
			<td width="80px"></td>
                                
                            <td>  Users->id </td>
                            <td>  Users->id </td>
                                
                            <td>  Users->name </td>
                            <td>  Users->name </td>
                                
                            <td>  Users->email </td>
                            <td>  Users->email </td>
                                
                            <td>  Users->password </td>
                            <td>  Users->password </td>
                                
                            <td>  Users->telephone </td>
                            <td>  Users->telephone </td>
                                
                            <td>  Users->address </td>
                            <td>  Users->address </td>
                                
                            <td>  Users->ip </td>
                            <td>  Users->ip </td>
                                
                            <td>  Users->admin </td>
                            <td>  Users->admin </td>
                                
                            <td>  Users->date </td>
                            <td>  Users->date </td>
                                

                        
                        
                        {blog_entries}
                            <td><= ${title}->{body} ></td>
			{/blog_entries}
                        <td style="text-align:center" width="200px">
				<?php 
				    echo anchor(site_url('admin/users/view/'.$users->id),'View'); 
				    echo ' | '; 
				    echo anchor(site_url('admin/users/edit/'.$users->id),'Edit'); 
				    echo ' | '; 
				    echo anchor(site_url('admin/users/delete/'.$users->id),'Delete','onclick="javasciprt: return confirm(\'Are You Sure ?\')"'); 
				?>
			</td>
		</tr>
                <?php endforeach; ?>
        </tbody>
    </table>