<?php
/**
 * @var \CodeIgniter\Pager\PagerRenderer $pager
 */

$pager->setSurroundCount(0);

if ($pager->hasPrevious())
{
	echo '<link rel="prev" href="' . $pager->getPrevious() . '">' . PHP_EOL;
}

echo '<link rel="canonical" href="' . $pager->getCurrent() . '">' . PHP_EOL;

if ($pager->hasNext())
{
	echo '<link rel="next" href="' . $pager->getNext() . '">' . PHP_EOL;
}
