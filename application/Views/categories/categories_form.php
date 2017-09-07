<!doctype html>
<html>
    <body>
        <h2 style="margin-top:0px">Categories </h2>
        <form action="<?= $action; ?>" method="post">
	    <div class="form-group">
            <label for="character varying">Name <?php //echo form_error('name') ?></label>
            <input type="text" class="form-control" name="name" id="name" placeholder="Name" value="<?= $name; ?>" />
        </div>
	    <div class="form-group">
            <label for="date">Date <?php //echo form_error('date') ?></label>
            <input type="text" class="form-control" name="date" id="date" placeholder="Date" value="<?= $date; ?>" />
        </div>
	        <footer class="panel-footer text-right bg-light lter">
	            <?php if (!empty($id)) { ?>
	                <input type="hidden" name="id" value="<?= $id; ?>" /> 
	                <a href="<?= site_url('categories/delete/'. $id); ?>" class="confirm btn btn-danger btn-s-xs">Delete</a>
	    <?php } ?>
	    <button type="submit" class="btn btn-primary"><?= $button ?></button> 
	    <a href="<?= site_url('categories') ?>" class="btn btn-default">Cancel</a>
	        </footer>
	</form>
    </body>
</html>