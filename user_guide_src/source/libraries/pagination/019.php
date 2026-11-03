<?php $pager->setSurroundCount(1) ?>

<p>
    Showing <span class="font-medium"><?= $pager->getPerPageStart() ?></span>
    to <span class="font-medium"><?= $pager->getPerPageEnd() ?></span>
    of <span class="font-medium"><?= $pager->getTotal() ?></span> results
</p>
