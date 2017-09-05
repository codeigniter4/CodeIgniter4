<!doctype html>
<html>
    <head>
        <title></title>
        <link rel="stylesheet" href="<?php echo base_url('assets/bootstrap/css/bootstrap.min.css') ?>"/>
        <style>
            body{
                padding: 15px;
            }
        </style>
    </head>
    <body>
        <h2 style="margin-top:0px">Categories </h2>
        <form action="<?php echo $action; ?>" method="post">
	    <div class="form-group">
            <label for="character varying">Name <?php //echo form_error('name') ?></label>
            <input type="text" class="form-control" name="name" id="name" placeholder="Name" value="<?php echo $name; ?>" />
        </div>
	    <div class="form-group">
            <label for="date">Date <?php //echo form_error('date') ?></label>
            <input type="text" class="form-control" name="date" id="date" placeholder="Date" value="<?php echo $date; ?>" />
        </div>
	        <footer class="panel-footer text-right bg-light lter">
	            <?php if (!empty($id)) { ?>
	                <input type="hidden" name="id" value="<?php echo $id; ?>" /> 
	                <a href="<?php echo site_url('categories/delete/'. $id); ?>" class="confirm btn btn-danger btn-s-xs">Delete</a>
	    <?php } ?>
	    <button type="submit" class="btn btn-primary"><?php echo $button ?></button> 
	    <a href="<?php echo site_url('categories') ?>" class="btn btn-default">Cancel</a>
	        </footer>
	</form>
    </body>
</html>