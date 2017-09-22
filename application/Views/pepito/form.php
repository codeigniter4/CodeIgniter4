<h2>Create A Pepito</h2> 

<?= form_open(); ?>

Id            <input type='number' class='form-control' name='id' />
Name            <input type='text' class='form-control' name='name' />
Date            <input type='date' class='form-control' name='date' />


    <input type="submit" name="submit" value="Create Pepito" class="btn btn-primary" />
    &nbsp;or&nbsp;
    <a href="<?= site_url('pepito') ?>">Cancel</a>

<?= form_close(); ?>
