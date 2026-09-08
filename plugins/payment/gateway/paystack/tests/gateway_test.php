<?php

namespace paygw_paystack;

defined('MOODLE_INTERNAL') || die();

/**
 * Unit tests for the Paystack payment gateway (spec 5.12).
 *
 * @package paygw_paystack
 */
final class gateway_test extends \advanced_testcase {

    public function test_get_supported_currencies_includes_ngn(): void {
        $currencies = gateway::get_supported_currencies();

        $this->assertContains('NGN', $currencies);
        $this->assertContains('GHS', $currencies);
        $this->assertContains('USD', $currencies);
    }

    /**
     * validate_gateway_form()'s $form parameter is only there to satisfy the
     * core_payment\gateway contract — this implementation never calls a
     * method on it — so a constructor-less instance safely stands in for a
     * real (DB-persistent-backed) form object here.
     */
    protected function fake_form(): \core_payment\form\account_gateway {
        return (new \ReflectionClass(\core_payment\form\account_gateway::class))->newInstanceWithoutConstructor();
    }

    public function test_validate_gateway_form_blocks_enabling_without_keys(): void {
        $this->resetAfterTest();
        $form = $this->fake_form();

        $errors = [];
        gateway::validate_gateway_form($form, (object) ['enabled' => 1], [], $errors);
        $this->assertArrayHasKey('enabled', $errors);

        $errors = [];
        gateway::validate_gateway_form(
            $form,
            (object) ['enabled' => 1, 'publickey' => 'pk_test_x'],
            [],
            $errors
        );
        $this->assertArrayHasKey('enabled', $errors, 'Missing secretkey must still block enabling.');
    }

    public function test_validate_gateway_form_allows_enabling_with_keys(): void {
        $this->resetAfterTest();
        $form = $this->fake_form();

        $errors = [];
        gateway::validate_gateway_form(
            $form,
            (object) ['enabled' => 1, 'publickey' => 'pk_test_x', 'secretkey' => 'sk_test_x'],
            [],
            $errors
        );
        $this->assertArrayNotHasKey('enabled', $errors);
    }

    public function test_validate_gateway_form_allows_disabled_without_keys(): void {
        $this->resetAfterTest();
        $form = $this->fake_form();

        $errors = [];
        gateway::validate_gateway_form($form, (object) ['enabled' => 0], [], $errors);
        $this->assertArrayNotHasKey('enabled', $errors);
    }

    /**
     * The account configuration form (business name/public key/secret
     * key/environment) must render without a fatal against a real,
     * DB-backed payment account — this is what Site administration > Payment
     * accounts actually builds.
     */
    public function test_add_configuration_to_gateway_form(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        $account = $this->getDataGenerator()->get_plugin_generator('core_payment')
            ->create_payment_account(['gateways' => 'paystack']);
        $gatewaypersistent = \core_payment\account_gateway::get_record([
            'accountid' => $account->get('id'),
            'gateway' => 'paystack',
        ]);
        $this->assertNotFalse($gatewaypersistent);

        $form = new \core_payment\form\account_gateway(null, ['persistent' => $gatewaypersistent]);
        $mform = $form->get_mform();

        $this->assertTrue($mform->elementExists('businessname'));
        $this->assertTrue($mform->elementExists('publickey'));
        $this->assertTrue($mform->elementExists('secretkey'));
        $this->assertTrue($mform->elementExists('environment'));
    }
}
