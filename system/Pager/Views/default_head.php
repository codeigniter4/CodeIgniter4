<?php
/**
 * @var \CodeIgniter\Pager\Pager $pager
 */

if ($pager->getPreviousPageURI())
{
	echo '<link rel="prev" href="' . $pager->getPreviousPageURI() . '">' . PHP_EOL;
}

echo '<link rel="canonical" href="' . $pager->getPageURI($pager->getCurrentPage()) . '">' . PHP_EOL;

if ($pager->getNextPageURI())
{
	echo '<link rel="next" href="' . $pager->getNextPageURI() . '">' . PHP_EOL;
}
