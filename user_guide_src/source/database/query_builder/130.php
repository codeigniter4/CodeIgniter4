<?php

$builder->likeAny(['title', 'body', 'summary'], $match);

/*
 * WHERE (
 *     `title` LIKE '%match%' ESCAPE '!'
 *     OR `body` LIKE '%match%' ESCAPE '!'
 *     OR `summary` LIKE '%match%' ESCAPE '!'
 * )
 */
