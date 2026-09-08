<?php

namespace paygw_paystack;

defined('MOODLE_INTERNAL') || die();

/**
 * Paystack payment gateway (spec 5.12).
 *
 * Modelled on Moodle core's bundled PayPal gateway
 * (payment/gateway/paypal/classes/gateway.php) — same base class and form
 * conventions, Paystack-specific fields and currencies.
 */
class gateway extends \core_payment\gateway {

    public static function get_supported_currencies(): array {
        // Paystack's currently supported settlement currencies
        // (https://paystack.com/docs/payments/accept-payments/#supported-currencies).
        // NGN is the primary currency per spec 5.12 ("NGN-denominated fees").
        return ['NGN', 'GHS', 'ZAR', 'KES', 'USD'];
    }

    /**
     * Configuration form for the gateway instance.
     *
     * @param \core_payment\form\account_gateway $form
     */
    public static function add_configuration_to_gateway_form(\core_payment\form\account_gateway $form): void {
        $mform = $form->get_mform();

        $mform->addElement('text', 'businessname', get_string('businessname', 'paygw_paystack'));
        $mform->setType('businessname', PARAM_TEXT);
        $mform->addHelpButton('businessname', 'businessname', 'paygw_paystack');

        $mform->addElement('text', 'publickey', get_string('publickey', 'paygw_paystack'));
        $mform->setType('publickey', PARAM_TEXT);
        $mform->addHelpButton('publickey', 'publickey', 'paygw_paystack');

        $mform->addElement('passwordunmask', 'secretkey', get_string('secretkey', 'paygw_paystack'));
        $mform->setType('secretkey', PARAM_TEXT);
        $mform->addHelpButton('secretkey', 'secretkey', 'paygw_paystack');

        $options = [
            'live' => get_string('live', 'paygw_paystack'),
            'test' => get_string('test', 'paygw_paystack'),
        ];
        $mform->addElement('select', 'environment', get_string('environment', 'paygw_paystack'), $options);
        $mform->addHelpButton('environment', 'environment', 'paygw_paystack');
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
