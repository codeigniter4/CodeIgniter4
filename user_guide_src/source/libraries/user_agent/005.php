<?php

if ($agent->isReferral()) {
    echo $agent->referrer();
}
