<?php $pager->setSurroundCount(2) ?>

<nav aria-label="<?= lang('Pager.pageNavigation') ?>">
	<ul class="pagination">
		<?php if ($pager->hasPrevious()) : ?>
			<li>
				<a href="<?= $pager->getFirst() ?>" aria-label="<?= lang('Pager.first') ?>">
					<span aria-hidden="true"><?= lang('Pager.first') ?></span>
				</a>
			</li>
			<li>
				<a href="<?= $pager->getPrevious() ?>" aria-label="<?= lang('Pager.previous') ?>">
					<span aria-hidden="true">&laquo;</span>
				</a>
			</li>
		<?php endif ?>

		<?php foreach ($pager->links() as $link) : ?>
			<li <?= $link['active'] ? 'class="active"' : '' ?>>
				<a href="<?= $link['uri'] ?>">
					<?= $link['title'] ?>
				</a>
			</li>
		<?php endforeach ?>

		<?php if ($pager->hasNext()) : ?>
			<li>
				<a href="<?= $pager->getNext() ?>" aria-label="<?= lang('Pager.next') ?>">
					<span aria-hidden="true">&raquo;</span>
				</a>
			</li>
			<li>
				<a href="<?= $pager->getLast() ?>" aria-label="<?= lang('Pager.last') ?>">
					<span aria-hidden="true"><?= lang('Pager.last') ?></span>
				</a>
			</li>
		<?php endif ?>
	</ul>
</nav>
