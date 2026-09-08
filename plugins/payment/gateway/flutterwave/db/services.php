<?php
defined('MOODLE_INTERNAL') || die();

$functions = [
    'paygw_flutterwave_get_config_for_js' => [
        'classname' => 'paygw_flutterwave\external\get_config_for_js',
        'classpath' => '',
        'description' => 'Returns the configuration settings the Flutterwave Inline JS checkout needs client-side.',
        'type' => 'read',
        'ajax' => true,
    ],
    'paygw_flutterwave_transaction_complete' => [
        'classname' => 'paygw_flutterwave\external\transaction_complete',
        'classpath' => '',
        'description' => 'Verifies a Flutterwave transaction server-side and, if genuine, delivers the paid-for item.',
        'type' => 'write',
        'ajax' => true,
        'loginrequired' => true,
    ],
];
