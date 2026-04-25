<?php

$result = service('locks')
    ->create('reports.daily-export', 300)
    ->run(static fn () => build_daily_report());
