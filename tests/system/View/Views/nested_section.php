<?php declare(strict_types=1);
$this->extend('layout'); ?>

<?php $this->section('content'); ?>
<p>Second</p>

    <?php $this->section('content'); ?>
    <p>First</p>
    <?php $this->endSection(); ?>

<?php $this->endSection(); ?>

<?php $this->section('content'); ?>
<p>Third</p>
<?php $this->endSection(); ?>
