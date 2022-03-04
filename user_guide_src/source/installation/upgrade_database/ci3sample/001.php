<?php

$query = $this->db->select('title')
             ->where('id', $id)
             ->limit(10, 20)
             ->get('mytable');
