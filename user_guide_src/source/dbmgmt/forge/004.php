<?php

$forge->createDatabase('my_db', true);
/*
 * gives CREATE DATABASE IF NOT EXISTS `my_db`
 * or will check if a database exists
 */
