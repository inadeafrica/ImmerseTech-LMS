<?php
defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Flutterwave';
$string['gatewayname'] = 'Flutterwave';
$string['gatewaydescription'] = 'Flutterwave is an authorized payment gateway provider for NGN-denominated fees, with broader West/Central/East African currency coverage (spec 5.12, 5.19).';

$string['businessname'] = 'Business name';
$string['businessname_help'] = 'The business name shown on the Flutterwave checkout.';
$string['publickey'] = 'Public key';
$string['publickey_help'] = 'The public key from your Flutterwave dashboard (Settings > API Keys).';
$string['secretkey'] = 'Secret key';
$string['secretkey_help'] = 'The secret key from your Flutterwave dashboard. Used server-side only, to verify transactions — never exposed to the browser.';
$string['secrethash'] = 'Webhook secret hash';
$string['secrethash_help'] = 'The secret hash configured against your Flutterwave webhook, used to verify the verif-hash header on incoming webhook calls.';
$string['environment'] = 'Environment';
$string['environment_help'] = 'Whether to use Flutterwave live keys or test keys.';
$string['live'] = 'Live';
$string['test'] = 'Test';

$string['cannotfetchtransaction'] = 'Could not verify the transaction with Flutterwave.';
$string['paymentnotcleared'] = 'Payment has not been cleared by Flutterwave.';
$string['amountmismatch'] = 'Paid amount does not match the expected cost.';
$string['internalerror'] = 'An internal error occurred while processing your payment.';

$string['privacy:metadata'] = 'The Flutterwave payment gateway plugin does not store personal data, only the Flutterwave transaction identifiers for each completed payment.';
$string['privacy:metadata:paygw_flutterwave'] = 'Stores the Flutterwave transaction identifiers against a Moodle payment record.';
$string['privacy:metadata:paygw_flutterwave:paymentid'] = 'The id of the payment record in the core payments table.';
$string['privacy:metadata:paygw_flutterwave:fw_transactionid'] = 'The Flutterwave transaction id.';
$string['privacy:metadata:paygw_flutterwave:fw_txref'] = 'The client-generated transaction reference, if provided.';
