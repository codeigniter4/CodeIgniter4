<?php

$builder->select('*')->from('my_table')
    ->groupStart()
        ->where('a', 'a')
        ->orGroupStart()
            ->where('b', 'b')
            ->where('c', 'c')
        ->groupEnd()
    ->groupEnd()
    ->where('d', 'd')
    ->get();
/*
 * Generates:
 * SELECT * FROM (`my_table`) WHERE ( `a` = 'a' OR ( `b` = 'b' AND `c` = 'c' ) ) AND `d` = 'd'
 */
