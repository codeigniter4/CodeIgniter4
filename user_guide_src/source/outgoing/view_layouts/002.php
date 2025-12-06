<?= $this->setVar('title', 'Homepage')->extend('default') ?>

<?= $this->setVar('hideNavbar', true)->section('content') ?>
    <h1>Items List</h1>
    <?php foreach ($items as $item): ?>
    <?= $this->setData(['item' => $item])->include('item_card') ?>
    <?php endforeach ?>
<?= $this->endSection() ?>
