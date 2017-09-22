<div><h4>Users </h4></div>
<form action="<?php echo $action; ?>" method="post">
    <div class="form-group">
        <label for="character varying">Name <?php echo form_error('name') ?></label>
        <input type="text" class="form-control" name="name" id="name" placeholder="Name" value="<?php echo $name; ?>" />
    </div>
    <div class="form-group">
        <label for="character varying">Email <?php echo form_error('email') ?></label>
        <input type="text" class="form-control" name="email" id="email" placeholder="Email" value="<?php echo $email; ?>" />
    </div>
    <div class="form-group">
        <label for="character varying">Password <?php echo form_error('password') ?></label>
        <input type="text" class="form-control" name="password" id="password" placeholder="Password" value="<?php echo $password; ?>" />
    </div>
    <div class="form-group">
        <label for="character varying">Telephone <?php echo form_error('telephone') ?></label>
        <input type="text" class="form-control" name="telephone" id="telephone" placeholder="Telephone" value="<?php echo $telephone; ?>" />
    </div>
    <div class="form-group">
        <label for="character varying">Address <?php echo form_error('address') ?></label>
        <input type="text" class="form-control" name="address" id="address" placeholder="Address" value="<?php echo $address; ?>" />
    </div>
    <div class="form-group">
        <label for="character varying">Ip <?php echo form_error('ip') ?></label>
        <input type="text" class="form-control" name="ip" id="ip" placeholder="Ip" value="<?php echo $ip; ?>" />
    </div>
    <div class="form-group">
        <label for="integer">Admin <?php echo form_error('admin') ?></label>
        <input type="text" class="form-control" name="admin" id="admin" placeholder="Admin" value="<?php echo $admin; ?>" />
    </div>
    <div class="form-group">
        <label for="date">Date <?php echo form_error('date') ?></label>
        <input type="text" class="form-control" name="date" id="date" placeholder="Date" value="<?php echo $date; ?>" />
    </div>
    <footer class="panel-footer text-right bg-light lter">
        <?php if (!empty($id)) { ?>
            <input type="hidden" name="id" value="<?php echo $id; ?>" /> 
            <a href="<?php echo site_url('admin/users/delete/.id'); ?>" class="confirm btn btn-danger btn-s-xs">Delete</a>
        <?php } ?>
        <button type="submit" class="btn btn-primary"><?php echo $button ?></button> 
        <a href="<?php echo site_url('admin/users') ?>" class="btn btn-warning">Cancel</a>
    </footer>
</form>