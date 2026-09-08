<?php

namespace paygw_flutterwave;

defined('MOODLE_INTERNAL') || die();

/**
 * Flutterwave payment gateway (spec 5.12).
 *
 * Modelled on Moodle core's bundled PayPal gateway
 * (payment/gateway/paypal/classes/gateway.php) — same base class and form
 * conventions, Flutterwave-specific fields and currencies.
 */
class gateway extends \core_payment\gateway {

    public static function get_supported_currencies(): array {
        // Flutterwave's currently supported settlement currencies for its
        // standard checkout across West/Central/East/Southern Africa
        // (https://developer.flutterwave.com/docs/making-payments/standard).
        // NGN is the primary currency per spec 5.12; the wider set matters
        // for ImmerseTech's West/Central Africa reach (spec 5.19).
        return ['NGN', 'GHS', 'KES', 'UGX', 'TZS', 'ZAR', 'XAF', 'XOF', 'USD'];
    }

    /**
     * Configuration form for the gateway instance.
     *
     * @param \core_payment\form\account_gateway $form
     */
    public static function add_configuration_to_gateway_form(\core_payment\form\account_gateway $form): void {
        $mform = $form->get_mform();

        $mform->addElement('text', 'businessname', get_string('businessname', 'paygw_flutterwave'));
        $mform->setType('businessname', PARAM_TEXT);
        $mform->addHelpButton('businessname', 'businessname', 'paygw_flutterwave');

        $mform->addElement('text', 'publickey', get_string('publickey', 'paygw_flutterwave'));
        $mform->setType('publickey', PARAM_TEXT);
        $mform->addHelpButton('publickey', 'publickey', 'paygw_flutterwave');

        $mform->addElement('passwordunmask', 'secretkey', get_string('secretkey', 'paygw_flutterwave'));
        $mform->setType('secretkey', PARAM_TEXT);
        $mform->addHelpButton('secretkey', 'secretkey', 'paygw_flutterwave');

        $mform->addElement('passwordunmask', 'secrethash', get_string('secrethash', 'paygw_flutterwave'));
        $mform->setType('secrethash', PARAM_TEXT);
        $mform->addHelpButton('secrethash', 'secrethash', 'paygw_flutterwave');

        $options = [
            'live' => get_string('live', 'paygw_flutterwave'),
            'test' => get_string('test', 'paygw_flutterwave'),
        ];
        $mform->addElement('select', 'environment', get_string('environment', 'paygw_flutterwave'), $options);
        $mform->addHelpButton('environment', 'environment', 'paygw_flutterwave');
    }

    /**
     * Validates the gateway configuration form.
     *
     * @param \core_payment\form\account_gateway $form
     * @param \stdClass $data
     * @param array $files
     * @param array $errors form errors (passed by reference)
     */
    public static function validate_gateway_form(
        \core_payment\form\account_gateway $form,
        \stdClass $data,
        array $files,
        array &$errors
    ): void {
        if ($data->enabled && (empty($data->publickey) || empty($data->secretkey))) {
            $errors['enabled'] = get_string('gatewaycannotbeenabled', 'payment');
        }
    }
}
