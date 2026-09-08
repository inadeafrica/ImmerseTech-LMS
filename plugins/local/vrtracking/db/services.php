<?php
defined('MOODLE_INTERNAL') || die();

$functions = [
    'local_vrtracking_record_completion' => [
        'classname'    => 'local_vrtracking\external\record_completion',
        'methodname'   => 'execute',
        'description'  => 'Records a VR module completion/progress event reported by the VR partner platform and, on completion, updates the linked competency (spec 5.4, 9.2).',
        'type'         => 'write',
        'ajax'         => false,
        'capabilities' => 'local/vrtracking:receivewebhook',
    ],
];

// A dedicated, restricted-user external service the VR partner platform
// authenticates against with its own token — kept separate from Moodle's
// general-purpose "Moodle mobile web service" so a leaked partner token
// can't be used for anything beyond posting completion events.
$services = [
    'ImmerseTech VR Partner Integration' => [
        'functions'      => ['local_vrtracking_record_completion'],
        'restrictedusers' => 1,
        'enabled'        => 1,
        'shortname'      => 'local_vrtracking_partner',
    ],
];
