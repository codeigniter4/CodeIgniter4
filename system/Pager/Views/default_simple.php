<?php $pager->setSurroundCount(0) ?>
<nav>
	<ul class="pager">
		<li <?= $pager->hasPrevious() ? '' : 'class="disabled"' ?>>
			<a href="<?= $pager->getPrevious() ?? '#' ?>" aria-label="Previous">
				<span aria-hidden="true">Older</span>
			</a>
		</li>
		<li <?= $pager->hasNext() ? '' : 'class="disabled"' ?>>
			<a href="<?= $pager->getnext() ?? '#' ?>" aria-label="Next">
				<span aria-hidden="true">Newer</span>
			</a>
		</li>
	</ul>
</nav>