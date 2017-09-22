<h2><?= $action ?> Pepito</h2>

<?= form_open(); ?>

Id            <input type='number' name='id' class='form-control' value='<?= set_value('id', $item->id ?? '') ?>' />
Name            <input type='text' name='name' class='form-control' value='<?= set_value('name', $item->name ?? '' ) ?>' />
Date            <input type='date' name='date' class='form-control' value='<?= set_value('date', $item->date ?? '') ?>' />

<input type="submit" name="submit" class="btn btn-primary" value="Save Pepito" />
&nbsp;or&nbsp;
<a href="<?= site_url('pepito') ?>">Cancel</a>

<?= form_close(); ?>
