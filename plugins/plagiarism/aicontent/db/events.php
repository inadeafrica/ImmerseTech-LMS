<?php
defined('MOODLE_INTERNAL') || die();

$observers = [
    [
        'eventname' => '\mod_assign\event\assessable_submitted',
        'callback' => '\plagiarism_aicontent\observer::assignment_submitted',
    ],
];
