<?php

declare(strict_types=1);

namespace paygw_paystack\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;
use core_payment\helper;

defined('MOODLE_INTERNAL') || die();

/**
 * Returns the config values the Paystack Inline JS popup needs client-side.
 */
class get_config_for_js extends external_api {

    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'component' => new external_value(PARAM_COMPONENT, 'Component'),
            'paymentarea' => new external_value(PARAM_AREA, 'Payment area in the component'),
            'itemid' => new external_value(PARAM_INT, 'An identifier for payment area in the component'),
        ]);
    }

    public static function execute(string $component, string $paymentarea, int $itemid): array {
        self::validate_parameters(self::execute_parameters(), [
            'component' => $component,
            'paymentarea' => $paymentarea,
            'itemid' => $itemid,
        ]);

        $config = helper::get_gateway_configuration($component, $paymentarea, $itemid, 'paystack');
        $payable = helper::get_payable($component, $paymentarea, $itemid);
        $surcharge = helper::get_gateway_surcharge('paystack');

        return [
            'publickey' => $config['publickey'],
            'businessname' => $config['businessname'],
            'cost' => helper::get_rounded_cost($payable->get_amount(), $payable->get_currency(), $surcharge),
            'currency' => $payable->get_currency(),
        ];
    }

    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'publickey' => new external_value(PARAM_TEXT, 'Paystack public key'),
            'businessname' => new external_value(PARAM_TEXT, 'Business name shown on the Paystack popup'),
            'cost' => new external_value(PARAM_FLOAT, 'Cost with gateway surcharge'),
            'currency' => new external_value(PARAM_TEXT, 'Currency'),
        ]);
    }
}
