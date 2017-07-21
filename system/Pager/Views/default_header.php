<?php
$pager->setSurroundCount(0);
$links = '';
if ($pager->hasPrevious())
{
	$links .= '<' . $pager->getFirst() . '>; rel="first",';
	$links .= '<' . $pager->getPrevious() . '>; rel="prev"';
}
if ($pager->hasPrevious() && $pager->hasNext())
{
	$links .= ',';
}
if ($pager->hasNext())
{
	$links .= '<' . $pager->getNext() . '>; rel="next",';
	$links .= '<' . $pager->getLast() . '>; rel="last"';
}
echo $links;
