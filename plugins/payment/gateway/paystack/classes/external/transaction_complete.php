<?php

declare(strict_types=1);

namespace paygw_paystack\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;
use core_payment\helper;
use paygw_paystack\paystack_helper;

defined('MOODLE_INTERNAL') || die();

/**
 * Handles the client telling Moodle "the Paystack popup reported success" —
 * the transaction is only trusted once verify_transaction() confirms it
 * server-side against Paystack's own records, never from the client alone.
 */
class transaction_complete extends external_api {

    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'component' => new external_value(PARAM_COMPONENT, 'The component name'),
            'paymentarea' => new external_value(PARAM_AREA, 'Payment area in the component'),
            'itemid' => new external_value(PARAM_INT, 'The item id in the context of the component area'),
            'reference' => new external_value(PARAM_ALPHANUMEXT, 'The Paystack transaction reference'),
        ]);
    }

    public static function execute(string $component, string $paymentarea, int $itemid, string $reference): array {
        global $USER, $DB;

        self::validate_parameters(self::execute_parameters(), [
            'component' => $component,
            'paymentarea' => $paymentarea,
            'itemid' => $itemid,
            'reference' => $reference,
        ]);

        $config = (object) helper::get_gateway_configuration($component, $paymentarea, $itemid, 'paystack');
        $payable = helper::get_payable($component, $paymentarea, $itemid);
        $currency = $payable->get_currency();

        $surcharge = helper::get_gateway_surcharge('paystack');
        $amount = helper::get_rounded_cost($payable->get_amount(), $currency, $surcharge);

        $success = false;
        $message = '';

        try {
            $paystackhelper = new paystack_helper($config->secretkey);
            $transaction = $paystackhelper->verify_transaction($reference);
        } catch (\Exception $e) {
            debugging('Exception while verifying Paystack transaction: ' . $e->getMessage(), DEBUG_DEVELOPER);
            $transaction = null;
        }

        if (!$transaction) {
            $message = get_string('cannotfetchtransaction', 'paygw_paystack');
        } else if ($transaction['status'] !== paystack_helper::TRANSACTION_STATUS_SUCCESS) {
            $message = get_string('paymentnotcleared', 'paygw_paystack');
        } else if (
            // Paystack reports amounts in the currency's minor unit (kobo for NGN).
            (float) $transaction['amount'] !== round($amount * 100)
            || strtoupper($transaction['currency']) !== $currency
        ) {
            $message = get_string('amountmismatch', 'paygw_paystack');
        } else {
            try {
                $paymentid = helper::save_payment(
                    $payable->get_account_id(),
                    $component,
                    $paymentarea,
                    $itemid,
                    (int) $USER->id,
                    $amount,
                    $currency,
                    'paystack'
                );

                $record = new \stdClass();
                $record->paymentid = $paymentid;
                $record->ps_reference = $reference;
                $DB->insert_record('paygw_paystack', $record);

                helper::deliver_order($component, $paymentarea, $itemid, $paymentid, (int) $USER->id);
                $success = true;
            } catch (\Exception $e) {
                debugging('Exception while trying to process payment: ' . $e->getMessage(), DEBUG_DEVELOPER);
                $message = get_string('internalerror', 'paygw_paystack');
            }
        }

        return [
            'success' => $success,
            'message' => $message,
        ];
    }

    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Whether everything was successful or not'),
            'message' => new external_value(PARAM_RAW, 'Message (usually the error message)'),
        ]);
    }
}
