<form>
    <input name="name" type="text">
    <input name="email" type="text">
    <input name="password" type="password">

    <?= csrf_field() ?>
    <input type="submit" value="Save">
</form>
