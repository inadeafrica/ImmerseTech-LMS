<?php

declare(strict_types=1);

namespace paygw_flutterwave\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;
use core_payment\helper;
use paygw_flutterwave\flutterwave_helper;

defined('MOODLE_INTERNAL') || die();

/**
 * Handles the client telling Moodle "the Flutterwave checkout reported
 * success" — the transaction is only trusted once verify_transaction()
 * confirms it server-side against Flutterwave's own records, never from
 * the client alone.
 */
class transaction_complete extends external_api {

    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'component' => new external_value(PARAM_COMPONENT, 'The component name'),
            'paymentarea' => new external_value(PARAM_AREA, 'Payment area in the component'),
            'itemid' => new external_value(PARAM_INT, 'The item id in the context of the component area'),
            'transactionid' => new external_value(PARAM_ALPHANUMEXT, 'The Flutterwave transaction id'),
        ]);
    }

    public static function execute(string $component, string $paymentarea, int $itemid, string $transactionid): array {
        global $USER, $DB;

        self::validate_parameters(self::execute_parameters(), [
            'component' => $component,
            'paymentarea' => $paymentarea,
            'itemid' => $itemid,
            'transactionid' => $transactionid,
        ]);

        $config = (object) helper::get_gateway_configuration($component, $paymentarea, $itemid, 'flutterwave');
        $payable = helper::get_payable($component, $paymentarea, $itemid);
        $currency = $payable->get_currency();

        $surcharge = helper::get_gateway_surcharge('flutterwave');
        $amount = helper::get_rounded_cost($payable->get_amount(), $currency, $surcharge);

        $success = false;
        $message = '';

        try {
            $fwhelper = new flutterwave_helper($config->secretkey);
            $transaction = $fwhelper->verify_transaction($transactionid);
        } catch (\Exception $e) {
            debugging('Exception while verifying Flutterwave transaction: ' . $e->getMessage(), DEBUG_DEVELOPER);
            $transaction = null;
        }

        if (!$transaction) {
            $message = get_string('cannotfetchtransaction', 'paygw_flutterwave');
        } else if ($transaction['status'] !== flutterwave_helper::TRANSACTION_STATUS_SUCCESSFUL) {
            $message = get_string('paymentnotcleared', 'paygw_flutterwave');
        } else if (
            (float) $transaction['amount'] < $amount
            || strtoupper($transaction['currency']) !== $currency
        ) {
            $message = get_string('amountmismatch', 'paygw_flutterwave');
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
                    'flutterwave'
                );

                $record = new \stdClass();
                $record->paymentid = $paymentid;
                $record->fw_transactionid = $transactionid;
                $record->fw_txref = $transaction['tx_ref'] ?? '';
                $DB->insert_record('paygw_flutterwave', $record);

                helper::deliver_order($component, $paymentarea, $itemid, $paymentid, (int) $USER->id);
                $success = true;
            } catch (\Exception $e) {
                debugging('Exception while trying to process payment: ' . $e->getMessage(), DEBUG_DEVELOPER);
                $message = get_string('internalerror', 'paygw_flutterwave');
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
