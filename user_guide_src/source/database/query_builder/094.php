<?php

$builder->where('id', $id);
$builder->delete();
/*
 * Produces:
 * DELETE FROM mytable
 * WHERE id = $id
 */
