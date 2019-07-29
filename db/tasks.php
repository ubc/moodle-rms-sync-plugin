<?php

defined('MOODLE_INTERNAL') || die();

$tasks = array(
    array(
        'classname' => 'tool_hrsync\task\hr_sync',
        'blocking' => 0,
        'minute' => '5',
        'hour' => '18',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*',
    ),
);