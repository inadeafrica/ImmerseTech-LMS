<?php
defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Paystack';
$string['gatewayname'] = 'Paystack';
$string['gatewaydescription'] = 'Paystack is an authorized payment gateway provider for NGN-denominated fees (spec 5.12).';

$string['businessname'] = 'Business name';
$string['businessname_help'] = 'The business name shown on the Paystack payment popup.';
$string['publickey'] = 'Public key';
$string['publickey_help'] = 'The public key from your Paystack dashboard (Settings > API Keys & Webhooks).';
$string['secretkey'] = 'Secret key';
$string['secretkey_help'] = 'The secret key from your Paystack dashboard. Used server-side only, to verify transactions — never exposed to the browser.';
$string['environment'] = 'Environment';
$string['environment_help'] = 'Whether to use Paystack live keys or test keys.';
$string['live'] = 'Live';
$string['test'] = 'Test';

$string['cannotfetchtransaction'] = 'Could not verify the transaction with Paystack.';
$string['paymentnotcleared'] = 'Payment has not been cleared by Paystack.';
$string['amountmismatch'] = 'Paid amount does not match the expected cost.';
$string['internalerror'] = 'An internal error occurred while processing your payment.';

$string['privacy:metadata'] = 'The Paystack payment gateway plugin does not store personal data, only the Paystack transaction reference for each completed payment.';
$string['privacy:metadata:paygw_paystack'] = 'Stores the Paystack transaction reference against a Moodle payment record.';
$string['privacy:metadata:paygw_paystack:paymentid'] = 'The id of the payment record in the core payments table.';
$string['privacy:metadata:paygw_paystack:ps_reference'] = 'The Paystack transaction reference.';
