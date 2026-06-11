<?php

$expected = 'SELECT * FROM "jobs" WHERE "id" = 1';
$actual   = $builder->where('id', 1)->getCompiledSelect();

$this->assertSqlEquals($expected, $actual);
