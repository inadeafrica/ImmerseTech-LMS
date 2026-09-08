<?php
defined('MOODLE_INTERNAL') || die();

$capabilities = [
    // Held by the webservice user the VR partner platform authenticates as
    // (spec 9.2) — grants nothing else, deliberately, since the webhook
    // token should not double as a general admin credential.
    'local/vrtracking:receivewebhook' => [
        'riskbitmask' => RISK_DATALOSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [],
    ],

    // Instructors and Qualified Assessors reviewing VR session logs for
    // competency sign-off (spec 5.4, 5.6).
    'local/vrtracking:viewsessions' => [
        'captype' => 'read',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => [
            'editingteacher' => CAP_ALLOW,
            'teacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
        ],
    ],
];
