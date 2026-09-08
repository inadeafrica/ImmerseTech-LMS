<?php
defined('MOODLE_INTERNAL') || die();

$functions = [
    'paygw_paystack_get_config_for_js' => [
        'classname' => 'paygw_paystack\external\get_config_for_js',
        'classpath' => '',
        'description' => 'Returns the configuration settings the Paystack Inline JS popup needs client-side.',
        'type' => 'read',
        'ajax' => true,
    ],
    'paygw_paystack_transaction_complete' => [
        'classname' => 'paygw_paystack\external\transaction_complete',
        'classpath' => '',
        'description' => 'Verifies a Paystack transaction server-side and, if genuine, delivers the paid-for item.',
        'type' => 'write',
        'ajax' => true,
        'loginrequired' => true,
    ],
];
