<?php

$query = $builder->get();

foreach ($query->getResult() as $row) {
    echo $row->title;
}
